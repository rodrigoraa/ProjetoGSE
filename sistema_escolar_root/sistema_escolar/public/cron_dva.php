<?php
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/src/Core/Helpers.php';

if (file_exists(ROOT_PATH . '/.env')) {
    carregar_env(ROOT_PATH . '/.env');
}

require_once ROOT_PATH . '/src/Core/Database.php';
require_once ROOT_PATH . '/src/Core/EmailService.php';

try {
    $pdo = \src\Core\Database::getConnection();

    $dataAlvo = date('Y-m-d', strtotime('+15 days'));

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
        $html = "<h2>⚠️ Aviso de Vencimento de DVA</h2>";
        $html .= "<p>As seguintes DVAs vencem nos próximos 15 dias (até <strong>" . date('d/m/Y', strtotime($dataAlvo)) . "</strong>):</p>";
        $html .= "<ul>";

        foreach ($lista as $l) {
            $dataVenc = date('d/m/Y', strtotime($l['data_vencimento']));
            $turma = !empty($l['nome_turma']) ? $l['nome_turma'] : 'Sem Turma';

            $html .= "<li><strong>{$l['nome_completo']}</strong> ({$turma}) - Vence em: <strong>{$dataVenc}</strong></li>";
        }
        $html .= "</ul>";

        $mail = new EmailService();

        try {
            $mail->enviar('eesjcpi@gmail.com', 'Aviso de Vencimento: DVA', $html);
            echo "SUCESSO: E-mail enviado com " . count($lista) . " avisos de DVA prestes a vencer.\n";
        } catch (Exception $e) {
            error_log("CRON (DVA): Falha no envio do email - " . $e->getMessage());
            echo "ERRO AO ENVIAR E-MAIL. Verifique o php_errors.log.\n";
        }
    } else {
        echo "TUDO OK: Nenhuma DVA vence nos próximos 15 dias.\n";
    }
} catch (Exception $e) {
    error_log("CRON (DVA): Erro Crítico ao rodar automação - " . $e->getMessage());
    echo "ERRO FATAL: Verifique o log do PHP.\n";
    exit(1);
}
