<?php
// 1. Caminho direto do seu .env (conforme você postou)
$dbPath = "C:/Users/alenc/OneDrive/Documentos/ProjetosEscola/sistema_escolar_root/sistema_escolar/database/secretaria.db";

try {
    // 2. Conecta ao banco de dados
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Iniciando atualização do banco de dados...</h3>";

    // 3. Executa o comando para adicionar a coluna
    $sql = "ALTER TABLE contrato_folhas ADD COLUMN modificado_manualmente INTEGER DEFAULT 0";
    $pdo->exec($sql);

    echo "<p style='color: green;'>✅ Coluna 'modificado_manualmente' adicionada com sucesso!</p>";
    echo "<p>Você já pode apagar este arquivo (update_db.php) por segurança.</p>";

} catch (PDOException $e) {
    // Se a coluna já existir, ele vai cair aqui
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "<p style='color: orange;'>⚠️ A coluna já existe no banco de dados.</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao atualizar: " . $e->getMessage() . "</p>";
    }
}