<?php
require_once ROOT_PATH . '/src/Models/Sistema.php';

class SistemaController extends Controller
{

    public function logs()
    {
        $this->verificarAdmin();
        $sistemaModel = new Sistema();

        $mensagem = '';
        if (isset($_POST['acao']) && $_POST['acao'] == 'limpar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $qtd = $sistemaModel->limparLogsAntigos(365);
            if ($qtd !== false) {
                $mensagem = "<p class='success-message'>Limpeza realizada: $qtd registros antigos apagados.</p>";
                registrar_log(Model::getConexao(), "Sistema", "Limpou $qtd logs antigos.");
            }
        }

        $logs = $sistemaModel->listarLogs(500);
        $this->view('sistema/logs', ['logs' => $logs, 'mensagem' => $mensagem]);
    }

    public function backups() {
        $this->verificarAdmin();
        $sistemaModel = new Sistema();
        $mensagem = '';
        
        $caminho_nuvem = 'G:/Meu Drive/Backups_Escola/'; 

        if (isset($_POST['acao']) && $_POST['acao'] == 'criar') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            
            $nome = $sistemaModel->criarBackupManual($caminho_nuvem);
            
            if ($nome) {
                $msg_extra = is_dir($caminho_nuvem) ? " (e na Nuvem ☁️)" : " (apenas Local)";
                $mensagem = "<p class='success-message'>✅ Backup criado: $nome $msg_extra</p>";
                registrar_log(Model::getConexao(), "Sistema", "Criou backup manual $msg_extra");
            } else {
                $mensagem = "<p class='error-message'>Erro ao criar backup.</p>";
            }
        }

        if (isset($_GET['apagar'])) {
            $arquivo_alvo = basename($_GET['apagar']);
            
            $lista_atual = $sistemaModel->listarBackups();
            $mais_recente = !empty($lista_atual) ? basename($lista_atual[0]) : null;

            if ($arquivo_alvo == $mais_recente) {
                $mensagem = "<p class='error-message'>⛔ Bloqueado: Você não pode apagar o backup mais recente.</p>";
            } else {
                $caminho = ROOT_PATH . '/database/backups/' . $arquivo_alvo;
                if (file_exists($caminho)) {
                    unlink($caminho);
                    registrar_log(Model::getConexao(), "Sistema", "Apagou backup: $arquivo_alvo");
                    
                    redirect('/sistema/backups');
                }
            }
        }

        if (isset($_GET['baixar'])) {
            $arquivo = basename($_GET['baixar']);
            $caminho = ROOT_PATH . '/database/backups/' . $arquivo;
            if (file_exists($caminho)) {
                registrar_log(Model::getConexao(), "Sistema", "Baixou backup: $arquivo");
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$arquivo.'"');
                header('Content-Length: ' . filesize($caminho));
                readfile($caminho);
                exit;
            }
        }

        $lista = $sistemaModel->listarBackups();

        $this->view('sistema/backups', [
            'lista' => $lista, 
            'mensagem' => $mensagem,
            'caminho_nuvem' => $caminho_nuvem
        ]);
    }

    private function verificarAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/painel');
            exit;
        }
    }
}