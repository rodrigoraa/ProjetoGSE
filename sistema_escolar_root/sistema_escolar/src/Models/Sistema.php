<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Sistema extends Model
{

    public function listarLogs($limite = 500)
    {
        $sql = "SELECT * FROM logs ORDER BY data_hora DESC LIMIT " . (int) $limite;
        return self::$pdo->query($sql)->fetchAll();
    }

    public function limparLogsAntigos($dias = 365)
    {
        try {
            $sql = "DELETE FROM logs WHERE data_hora < date('now', '-$dias days')";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            return false;
        }
    }

    public function criarBackupManual($caminhoNuvem = null)
    {
        $pasta_local = ROOT_PATH . '/database/backups/';
        $arquivo_banco = '/var/www/data/secretaria.db';

        if (!is_dir($pasta_local)) {
            mkdir($pasta_local, 0755, true);
        }

        $nome = 'escola_backup_MANUAL_' . date('Y-m-d_H-i-s') . '.db';

        if (copy($arquivo_banco, $pasta_local . $nome)) {
            if ($caminhoNuvem && is_dir($caminhoNuvem)) {
                copy($arquivo_banco, $caminhoNuvem . $nome);
            }
            return $nome;
        }
        return false;
    }

    public function listarBackups()
    {
        $pasta = ROOT_PATH . '/database/backups/';
        if (!is_dir($pasta))
            return [];

        $arquivos = glob($pasta . '*.db');

        usort($arquivos, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $arquivos;
    }
}