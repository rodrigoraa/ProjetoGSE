<?php
require_once ROOT_PATH . '/src/Models/Painel.php';
require_once ROOT_PATH . '/src/Models/Aluno.php';
require_once ROOT_PATH . '/src/Models/Certidao.php';

class PainelController extends Controller
{
    private $painelModel;
    private $alunoModel;
    private $certidaoModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->painelModel = new Painel();
        $this->alunoModel = new Aluno();
        $this->certidaoModel = new Certidao();
    }

    public function index()
    {
        $status_backup = $this->verificarStatusBackupLinux();

        $certidoes_alerta = $this->certidaoModel->buscarVencendoProximosDias(30);
        $vencidas         = $this->painelModel->getDvasVencidas();
        $a_vencer         = $this->painelModel->getDvasAVencer();

        $mesAtual = date('m');
        $diaHoje  = date('d');
        $anoAtual = date('Y');

        $aniversariantes_hoje = [];
        $lista_hoje = $this->alunoModel->getAniversariantesHoje($diaHoje, $mesAtual);

        foreach ($lista_hoje as $aluno) {
            $ano_nasc = date('Y', strtotime($aluno['data_nascimento']));
            $aluno['idade_nova'] = $anoAtual - $ano_nasc;
            $aniversariantes_hoje[] = $aluno;
        }

        $dados = [
            'nome_usuario'         => $_SESSION['usuario_nome'],
            'tipo_usuario'         => $_SESSION['usuario_tipo'],
            'msg_backup'           => $status_backup,

            'total_alunos'         => $this->painelModel->getTotalAlunos(),
            'total_sem_dva'        => $this->painelModel->getTotalSemDva(),
            'total_vencidas'       => count($vencidas),
            'total_avencer'        => count($a_vencer),

            'lista_sem_dva'        => $this->painelModel->getListaAlunosSemDva(),
            'lista_vencidas'       => $vencidas,
            'lista_avencer'        => $a_vencer,
            'lista_vigentes'       => $this->painelModel->getDvasVigentes(),

            'aniversariantes_mes'  => $this->alunoModel->getAniversariantesDoMes($mesAtual),
            'aniversariantes_hoje' => $aniversariantes_hoje,
            'certidoes_alerta'     => $certidoes_alerta
        ];

        $this->view('painel', $dados);
    }

    private function verificarStatusBackupLinux()
    {
        $diretorio_logs = '/var/backups/escola/logs/';

        if (!is_dir($diretorio_logs)) return "⚠️ Pasta de logs não acessível.";

        $logs = glob($diretorio_logs . 'backup_*.log');
        if (empty($logs)) return "⚠️ Nenhum log de backup encontrado.";

        usort($logs, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $ultimo_log = $logs[0];
        $data_log = date("d/m H:i", filemtime($ultimo_log));

        if (filemtime($ultimo_log) < (time() - 93600)) {
            return "❌ Backup atrasado! Último registro: $data_log";
        }

        return "✅ Sistema protegido. Último backup: $data_log";
    }
}
