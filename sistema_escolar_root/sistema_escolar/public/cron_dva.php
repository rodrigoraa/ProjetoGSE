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

function garantirColunaRecebeAvisosEmail(PDO $pdo)
{
    $colunas = $pdo->query("PRAGMA table_info(usuarios)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colunas as $coluna) {
        if (($coluna['name'] ?? '') === 'recebe_avisos_email') {
            return;
        }
    }

    $pdo->exec("ALTER TABLE usuarios ADD COLUMN recebe_avisos_email INTEGER NOT NULL DEFAULT 1");
}

function buscarDestinatariosAviso(PDO $pdo)
{
    $sql = "SELECT DISTINCT nome, email
            FROM usuarios
            WHERE email IS NOT NULL
              AND TRIM(email) <> ''
              AND recebe_avisos_email = 1
            ORDER BY nome";

    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_values(array_filter($usuarios, static function ($usuario) {
        return filter_var($usuario['email'] ?? '', FILTER_VALIDATE_EMAIL);
    }));
}

try {
    $pdo = \src\Core\Database::getConnection();
    garantirColunaRecebeAvisosEmail($pdo);
    $diasAntecedencia = (int) ($_ENV['ALERTA_DVA_DIAS'] ?? 15);

    if ($diasAntecedencia <= 0) {
        $diasAntecedencia = 15;
    }

    $dataAlvo = date('Y-m-d', strtotime('+' . $diasAntecedencia . ' days'));

    $sql = "SELECT a.nome_completo, t.nome_turma, d.data_vencimento
            FROM dvas d
            JOIN alunos a ON d.id_aluno = a.id
            LEFT JOIN turmas t ON a.id_turma = t.id
            WHERE d.data_vencimento <= ?
              AND d.data_vencimento >= date('now')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataAlvo]);
    $lista = $stmt->fetchAll();

    if (count($lista) > 0) {
        $html = "<h2>Aviso de Vencimento de DVA</h2>";
        $html .= "<p>As seguintes DVAs vencem nos próximos {$diasAntecedencia} dias (até <strong>" . date('d/m/Y', strtotime($dataAlvo)) . "</strong>):</p>";
        $html .= "<ul>";

        foreach ($lista as $l) {
            $dataVenc = date('d/m/Y', strtotime($l['data_vencimento']));
            $turma = !empty($l['nome_turma']) ? $l['nome_turma'] : 'Sem Turma';
            $html .= "<li><strong>{$l['nome_completo']}</strong> ({$turma}) - Vence em: <strong>{$dataVenc}</strong></li>";
        }

        $html .= "</ul>";

        $destinatarios = buscarDestinatariosAviso($pdo);

        if (count($destinatarios) === 0) {
            error_log('CRON (DVA): Nenhum usuário habilitado com e-mail válido foi encontrado para receber os avisos.');
            echo "ERRO DE CONFIGURAÇÃO: não há usuários habilitados com e-mail válido para receber os avisos.\n";
            exit(1);
        }

        $mail = new EmailService();
        $falhas = [];

        foreach ($destinatarios as $destinatario) {
            if (!$mail->enviar($destinatario['email'], 'Aviso de Vencimento: DVA', $html)) {
                $falhas[] = $destinatario['email'];
            }
        }

        if (count($falhas) === 0) {
            echo "SUCESSO: E-mail enviado para " . count($destinatarios) . " usuário(s) com " . count($lista) . " avisos de DVA.\n";
        } else {
            error_log("CRON (DVA): Falha no envio para: " . implode(', ', $falhas));
            echo "ERRO AO ENVIAR E-MAIL. Verifique o php_errors.log.\n";
            exit(1);
        }
    } else {
        echo "TUDO OK: Nenhuma DVA vence nos próximos {$diasAntecedencia} dias.\n";
    }
} catch (Exception $e) {
    error_log("CRON (DVA): Erro crítico ao rodar automação - " . $e->getMessage());
    echo "ERRO FATAL: Verifique o log do PHP.\n";
    exit(1);
}
