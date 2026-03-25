<?php
require_once ROOT_PATH . '/src/Core/Model.php';

class Sistema extends Model
{
    public function listarLogs($limite = 500)
    {
        try {
            $limite = (int) $limite;
            $sql = "SELECT * FROM logs ORDER BY data_hora DESC LIMIT $limite";
            return self::$pdo->query($sql)->fetchAll();
        } catch (Exception $e) {
            error_log("Erro no Model Sistema (listarLogs): " . $e->getMessage());
            return [];
        }
    }

    public function limparLogsAntigos($dias = 365)
    {
        try {
            $dias = (int) $dias;
            $sql = "DELETE FROM logs WHERE data_hora < date('now', '-$dias days')";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Erro no Model Sistema (limparLogsAntigos): " . $e->getMessage());
            return false;
        }
    }

    public function criarBackupManual($caminhoNuvem = null)
    {
        try {
            $pasta_local = ROOT_PATH . '/database/backups/';

            $arquivo_banco = $_ENV['DB_PATH'] ?? getenv('DB_PATH');

            if (!$arquivo_banco || !file_exists($arquivo_banco)) {
                throw new Exception("Arquivo de banco de dados original não encontrado para backup.");
            }

            if (!is_dir($pasta_local)) {
                mkdir($pasta_local, 0755, true);
            }

            $nome = 'escola_backup_MANUAL_' . date('Y-m-d_H-i-s') . '.db';
            $destino_local = $pasta_local . $nome;

            if (copy($arquivo_banco, $destino_local)) {
                if ($caminhoNuvem && is_dir($caminhoNuvem)) {
                    copy($arquivo_banco, rtrim($caminhoNuvem, '/') . '/' . $nome);
                }
                return $nome;
            }

            return false;
        } catch (Exception $e) {
            error_log("Erro CRÍTICO no Model Sistema (criarBackupManual): " . $e->getMessage());
            return false;
        }
    }

    public function listarBackups()
    {
        try {
            $pasta = ROOT_PATH . '/database/backups/';
            if (!is_dir($pasta)) return [];

            $arquivos = glob($pasta . '*.db');

            usort($arquivos, function ($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            return $arquivos;
        } catch (Exception $e) {
            error_log("Erro no Model Sistema (listarBackups): " . $e->getMessage());
            return [];
        }
    }
}
