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
            $pasta_local = $this->obterPastaBackups();

            $arquivo_banco = $_ENV['DB_PATH'] ?? getenv('DB_PATH');

            if (!$arquivo_banco || !file_exists($arquivo_banco)) {
                throw new Exception("Arquivo de banco de dados original não encontrado para backup.");
            }

            if (!is_dir($pasta_local)) {
                mkdir($pasta_local, 0755, true);
            }

            $nome = 'escola_backup_MANUAL_' . date('Y-m-d_H-i-s') . '.db';
            $destino_local = rtrim($pasta_local, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nome;

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
            $pasta = $this->obterPastaBackups();
            if (!is_dir($pasta)) return [];

            $arquivos = glob(rtrim($pasta, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.db') ?: [];
            $backups = [];

            foreach ($arquivos as $arquivo) {
                $backups[] = [
                    'caminho' => $arquivo,
                    'nome' => basename($arquivo),
                    'timestamp' => $this->obterTimestampBackup($arquivo),
                    'modificado_em' => filemtime($arquivo) ?: 0,
                    'tamanho' => filesize($arquivo) ?: 0,
                ];
            }

            usort($backups, function ($a, $b) {
                if ($a['timestamp'] === $b['timestamp']) {
                    return $b['modificado_em'] <=> $a['modificado_em'];
                }

                return $b['timestamp'] <=> $a['timestamp'];
            });

            return $backups;
        } catch (Exception $e) {
            error_log("Erro no Model Sistema (listarBackups): " . $e->getMessage());
            return [];
        }
    }

    public function obterPastaBackups()
    {
        $pasta = $_ENV['BACKUP_PATH'] ?? getenv('BACKUP_PATH');
        $pasta = $pasta ?: 'database/backups/';
        $pasta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($pasta));

        if ($this->caminhoAbsoluto($pasta)) {
            return rtrim($pasta, DIRECTORY_SEPARATOR);
        }

        return ROOT_PATH . DIRECTORY_SEPARATOR . trim($pasta, DIRECTORY_SEPARATOR);
    }

    private function caminhoAbsoluto($caminho)
    {
        return preg_match('/^[A-Za-z]:\\\\/', $caminho) === 1
            || substr($caminho, 0, 1) === DIRECTORY_SEPARATOR
            || substr($caminho, 0, 2) === '\\\\';
    }

    private function obterTimestampBackup($arquivo)
    {
        $nome = basename($arquivo);

        if (preg_match('/(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})(?:-(\d{2}))?/', $nome, $matches)) {
            $segundos = $matches[4] ?? '00';
            $timestamp = strtotime("{$matches[1]} {$matches[2]}:{$matches[3]}:{$segundos}");

            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return filemtime($arquivo) ?: 0;
    }
}
