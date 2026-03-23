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

$sql = "SELECT fornecedor, tipo_certidao, data_vencimento 
        FROM certidoes 
        WHERE data_vencimento <= ? 
        AND data_vencimento >= date('now')
        AND status = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$dataAlvo]);
$lista = $stmt->fetchAll();

if (count($lista) > 0) {
    $html = "<h2>📜 Alerta de Vencimento de Certidões</h2>";
    $html .= "<p>As seguintes certidões de fornecedores vencem nos próximos 15 dias (até <strong>" . date('d/m/Y', strtotime($dataAlvo)) . "</strong>):</p>";
    $html .= "<ul>";

    foreach ($lista as $l) {
        $dataVenc = date('d/m/Y', strtotime($l['data_vencimento']));
        $html .= "<li><strong>{$l['fornecedor']}</strong> - {$l['tipo_certidao']} (Vence em: $dataVenc)</li>";
    }

    $html .= "</ul>";
    $html .= "<p><br><em>Verifique o painel do sistema para baixar o PDF atual e providenciar a renovação.</em></p>";

    $mail = new EmailService();
    try {
        $mail->enviar('eesjcpi@gmail.com', 'Aviso de Vencimento: Certidões', $html);
        echo "SUCESSO: E-mail enviado com " . count($lista) . " avisos de certidão prestes a vencer.\n";
    } catch (Exception $e) {
        echo "ERRO AO ENVIAR E-MAIL: " . $e->getMessage() . "\n";
    }
} else {
    echo "TUDO OK: Nenhuma certidão ativa vence nos próximos 15 dias.\n";
}
