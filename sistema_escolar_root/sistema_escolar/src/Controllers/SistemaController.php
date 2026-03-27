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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'limpar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $qtd = $this->sistemaModel->limparLogsAntigos(365);

            if ($qtd !== false) {
                definir_flash('sucesso', 'Logs limpos', "Foram removidos {$qtd} registros antigos.");
                registrar_log(Model::getConexao(), 'Sistema', 'Realizou limpeza de logs (>365 dias).');
            } else {
                definir_flash('erro', 'Nao foi possivel limpar os logs', 'Tente novamente em alguns instantes.');
            }

            redirect('/sistema/logs');
            exit;
        }

        $logs = $this->sistemaModel->listarLogs(500);
        $this->view('sistema/logs', ['logs' => $logs]);
    }

    public function backups()
    {
        $caminho_nuvem = $_ENV['CLOUD_BACKUP_PATH'] ?? '/var/backups/escola/nuvem_manual/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = $this->sistemaModel->criarBackupManual($caminho_nuvem);

            if ($nome) {
                definir_flash('sucesso', 'Backup gerado', "Backup criado com sucesso: {$nome}");
                registrar_log(Model::getConexao(), 'Sistema', 'Criou backup manual via painel.');
            } else {
                definir_flash('erro', 'Nao foi possivel gerar o backup', 'Verifique as permissoes da pasta e tente novamente.');
            }

            redirect('/sistema/backups');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'apagar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $this->processarExclusaoBackup($_POST['arquivo'] ?? '');
        }

        if (isset($_GET['baixar'])) {
            $this->processarDownloadBackup($_GET['baixar']);
        }

        $lista = $this->sistemaModel->listarBackups();

        $this->view('sistema/backups', [
            'lista' => $lista,
            'caminho_nuvem' => $caminho_nuvem
        ]);
    }

    private function processarExclusaoBackup($filename)
    {
        $arquivo_alvo = basename($filename);
        $lista_atual = $this->sistemaModel->listarBackups();
        $mais_recente = !empty($lista_atual) ? basename($lista_atual[0]) : null;

        if ($arquivo_alvo === $mais_recente) {
            definir_flash('aviso', 'Acao protegida', 'O backup mais recente nao pode ser excluido.');
            redirect('/sistema/backups');
            exit;
        }

        $pasta_backups = $_ENV['BACKUP_PATH'] ?? 'database/backups/';
        $caminho = ROOT_PATH . '/' . rtrim($pasta_backups, '/') . '/' . $arquivo_alvo;

        if (file_exists($caminho) && unlink($caminho)) {
            definir_flash('sucesso', 'Backup excluido', "O arquivo {$arquivo_alvo} foi removido.");
            registrar_log(Model::getConexao(), 'Sistema', "Excluiu arquivo de backup: {$arquivo_alvo}");
        } else {
            definir_flash('erro', 'Nao foi possivel excluir o backup', 'O arquivo informado nao foi encontrado ou nao pode ser removido.');
        }

        redirect('/sistema/backups');
        exit;
    }

    private function processarDownloadBackup($filename)
    {
        $arquivo = basename($filename);
        $pasta_backups = $_ENV['BACKUP_PATH'] ?? 'database/backups/';
        $caminho = ROOT_PATH . '/' . rtrim($pasta_backups, '/') . '/' . $arquivo;

        if (!file_exists($caminho) || !is_file($caminho)) {
            definir_flash('erro', 'Arquivo nao encontrado', 'O backup solicitado nao esta mais disponivel.');
            redirect('/sistema/backups');
            exit;
        }

        registrar_log(Model::getConexao(), 'Sistema', "Download do backup: {$arquivo}");

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

    private function verificarAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/painel');
            exit;
        }
    }
}
