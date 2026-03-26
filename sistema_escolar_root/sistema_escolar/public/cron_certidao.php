<?php
define('ROOT_PATH', dirname(__DIR__));

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.');
}

require_once ROOT_PATH . '/src/Core/Helpers.php';

if (file_exists(ROOT_PATH . '/.env')) {
    carregar_env(ROOT_PATH . '/.env');
}

require_once ROOT_PATH . '/src/Core/Database.php';
require_once ROOT_PATH . '/src/Core/EmailService.php';

function buscarDestinatariosAviso(PDO $pdo)
{
    $sql = "SELECT DISTINCT nome, email
            FROM usuarios
            WHERE email IS NOT NULL
              AND TRIM(email) <> ''
            ORDER BY nome";

    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_values(array_filter($usuarios, static function ($usuario) {
        return filter_var($usuario['email'] ?? '', FILTER_VALIDATE_EMAIL);
    }));
}

try {
    $pdo = \src\Core\Database::getConnection();
    $diasAntecedencia = (int) ($_ENV['ALERTA_CERTIDAO_DIAS'] ?? 15);

    if ($diasAntecedencia <= 0) {
        $diasAntecedencia = 15;
    }

    $dataAlvo = date('Y-m-d', strtotime('+' . $diasAntecedencia . ' days'));

    $sql = "SELECT
                f.nome AS fornecedor,
                t.nome AS tipo_certidao,
                c.data_vencimento
            FROM certidoes c
            JOIN lista_fornecedores f ON f.id = c.id_fornecedor
            JOIN lista_tipos_certidao t ON t.id = c.id_tipo_certidao
            WHERE c.data_vencimento <= ?
              AND c.data_vencimento >= date('now')
              AND (c.status = 1 OR c.status IS NULL)
              AND (c.arquivado = 0 OR c.arquivado IS NULL)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataAlvo]);
    $lista = $stmt->fetchAll();

    if (count($lista) > 0) {
        $html = "<h2>Alerta de Vencimento de Certidões</h2>";
        $html .= "<p>As seguintes certidões de fornecedores vencem nos próximos {$diasAntecedencia} dias (até <strong>" . date('d/m/Y', strtotime($dataAlvo)) . "</strong>):</p>";
        $html .= "<ul>";

        foreach ($lista as $l) {
            $dataVenc = date('d/m/Y', strtotime($l['data_vencimento']));
            $html .= "<li><strong>{$l['fornecedor']}</strong> - {$l['tipo_certidao']} (Vence em: {$dataVenc})</li>";
        }

        $html .= "</ul>";
        $html .= "<p><br><em>Verifique o painel do sistema para baixar o PDF atual e providenciar a renovação.</em></p>";

        $destinatarios = buscarDestinatariosAviso($pdo);

        if (count($destinatarios) === 0) {
            error_log('CRON (Certidões): Nenhum usuário com e-mail válido foi encontrado para receber os avisos.');
            echo "ERRO DE CONFIGURAÇÃO: não há usuários com e-mail válido cadastrados no sistema.\n";
            exit(1);
        }

        $mail = new EmailService();
        $falhas = [];

        foreach ($destinatarios as $destinatario) {
            if (!$mail->enviar($destinatario['email'], 'Aviso de Vencimento: Certidões', $html)) {
                $falhas[] = $destinatario['email'];
            }
        }

        if (count($falhas) === 0) {
            echo "SUCESSO: E-mail enviado para " . count($destinatarios) . " usuário(s) com " . count($lista) . " avisos de certidão.\n";
        } else {
            error_log("CRON (Certidões): Falha no envio para: " . implode(', ', $falhas));
            echo "ERRO AO ENVIAR E-MAIL. Verifique o php_errors.log.\n";
            exit(1);
        }
    } else {
        echo "TUDO OK: Nenhuma certidão ativa vence nos próximos {$diasAntecedencia} dias.\n";
    }
} catch (Exception $e) {
    error_log("CRON (Certidões): Erro crítico ao rodar automação - " . $e->getMessage());
    echo "ERRO FATAL: Verifique o log do PHP.\n";
    exit(1);
}
