<?php
require_once ROOT_PATH . '/src/Models/Sistema.php';

class SistemaController extends Controller
{
    private $sistemaModel;

    public function __construct()
    {
        $this->verificarAdmin();
        $this->sistemaModel = new Sistema();
    }

    public function logs()
    {
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'limpar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $qtd = $this->sistemaModel->limparLogsAntigos(365);

            if ($qtd !== false) {
                $mensagem = "<div class='alert success'>Limpeza realizada: $qtd registros removidos.</div>";
                registrar_log(Model::getConexao(), "Sistema", "Realizou limpeza de logs (>365 dias).");
            }
        }

        $logs = $this->sistemaModel->listarLogs(500);
        $this->view('sistema/logs', ['logs' => $logs, 'mensagem' => $mensagem]);
    }

    public function backups()
    {
        $mensagem = '';
        $caminho_nuvem = $_ENV['CLOUD_BACKUP_PATH'] ?? '/var/backups/escola/nuvem_manual/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'criar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = $this->sistemaModel->criarBackupManual($caminho_nuvem);

            if ($nome) {
                $mensagem = "<div class='alert success'>Backup gerado com sucesso: <strong>" . e($nome) . "</strong></div>";
                registrar_log(Model::getConexao(), "Sistema", "Criou backup manual via painel.");
            } else {
                $mensagem = "<div class='alert error'>Erro ao gerar backup. Verifique as permissoes da pasta.</div>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'apagar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $this->processarExclusaoBackup($_POST['arquivo'] ?? '', $mensagem);
        }

        if (isset($_GET['baixar'])) {
            $this->processarDownloadBackup($_GET['baixar']);
        }

        $lista = $this->sistemaModel->listarBackups();

        $this->view('sistema/backups', [
            'lista' => $lista,
            'mensagem' => $mensagem,
            'caminho_nuvem' => $caminho_nuvem
        ]);
    }

    private function processarExclusaoBackup($filename, &$mensagem)
    {
        $arquivo_alvo = basename($filename);
        $lista_atual = $this->sistemaModel->listarBackups();
        $mais_recente = !empty($lista_atual) ? basename($lista_atual[0]) : null;

        if ($arquivo_alvo == $mais_recente) {
            $mensagem = "<div class='alert warning'>Protecao: o backup mais recente nao pode ser excluido.</div>";
            return;
        }

        $pasta_backups = $_ENV['BACKUP_PATH'] ?? 'database/backups/';
        $caminho = ROOT_PATH . '/' . rtrim($pasta_backups, '/') . '/' . $arquivo_alvo;

        if (file_exists($caminho) && unlink($caminho)) {
            registrar_log(Model::getConexao(), "Sistema", "Excluiu arquivo de backup: $arquivo_alvo");
            redirect('/sistema/backups?status=apagado');
            exit;
        }
    }

    private function processarDownloadBackup($filename)
    {
        $arquivo = basename($filename);

        $pasta_backups = $_ENV['BACKUP_PATH'] ?? 'database/backups/';
        $caminho = ROOT_PATH . '/' . rtrim($pasta_backups, '/') . '/' . $arquivo;

        if (file_exists($caminho)) {
            registrar_log(Model::getConexao(), "Sistema", "Download do backup: $arquivo");

            header('Content-Description: File Transfer');
            header('Content-Type: application/x-sqlite3');
            header('Content-Disposition: attachment; filename="' . $arquivo . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($caminho));

            if (ob_get_level()) {
                ob_end_clean();
            }
            flush();

            readfile($caminho);
            exit;
        }
    }

    private function verificarAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/painel');
            exit;
        }
    }
}
