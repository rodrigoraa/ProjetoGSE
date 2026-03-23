<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/src/Core/EmailService.php';

$dbFile = '/var/www/data/secretaria.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

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
        echo "ERRO AO ENVIAR E-MAIL: " . $e->getMessage() . "\n";
    }
} else {
    echo "TUDO OK: Nenhuma DVA vence nos próximos 15 dias.\n";
}
