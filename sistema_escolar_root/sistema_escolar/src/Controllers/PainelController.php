<?php
require_once ROOT_PATH . '/src/Models/Painel.php';
require_once ROOT_PATH . '/src/Models/Aluno.php';
require_once ROOT_PATH . '/src/Models/Certidao.php';
require_once ROOT_PATH . '/src/Models/Agenda.php'; // 1. Importado o Model da Agenda

class PainelController extends Controller
{
    private $painelModel;
    private $alunoModel;
    private $certidaoModel;
    private $agendaModel; // 2. Definida a propriedade

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->painelModel = new Painel();
        $this->alunoModel = new Aluno();
        $this->certidaoModel = new Certidao();
        $this->agendaModel = new Agenda();
    }

    public function index()
    {
        $status_backup = $this->verificarStatusBackupLinux();

        $certidoes_alerta = $this->certidaoModel->buscarVencendoProximosDias(30);
        $vencidas         = $this->painelModel->getDvasVencidas();
        $a_vencer         = $this->painelModel->getDvasAVencer();

        $alertas_agenda = $this->agendaModel->listarAlertasPainel();
        $feriados_painel = $this->listarFeriadosPainel(7);

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
            'certidoes_alerta'     => $certidoes_alerta,
            'alertas_agenda'       => $alertas_agenda,
            'feriados_painel'      => $feriados_painel
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

    private function listarFeriadosPainel($dias)
    {
        $hoje = new DateTimeImmutable('today');
        $limite = $hoje->modify('+' . (int)$dias . ' days');
        $anos = [(int)$hoje->format('Y')];

        if ($limite->format('Y') !== $hoje->format('Y')) {
            $anos[] = (int)$limite->format('Y');
        }

        $feriados = [];

        foreach (array_unique($anos) as $ano) {
            $feriados = array_merge(
                $feriados,
                $this->listarFeriadosNacionais($ano),
                $this->listarFeriadosMunicipaisVicentina($ano)
            );
        }

        $feriados = array_values(array_filter($feriados, function ($feriado) use ($hoje, $limite) {
            if (empty($feriado['date'])) {
                return false;
            }

            $data = DateTimeImmutable::createFromFormat('Y-m-d', $feriado['date']);

            return $data && $data >= $hoje && $data <= $limite;
        }));

        usort($feriados, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $feriados;
    }

    private function listarFeriadosNacionais($ano)
    {
        $chave = "feriados_$ano";

        if (!isset($_SESSION[$chave])) {
            $jsonFeriados = @file_get_contents("https://brasilapi.com.br/api/feriados/v1/{$ano}");
            $_SESSION[$chave] = $jsonFeriados ? json_decode($jsonFeriados, true) : [];
        }

        return array_map(function ($feriado) {
            return [
                'date' => $feriado['date'] ?? '',
                'name' => $feriado['name'] ?? 'Feriado Nacional',
                'type' => 'nacional'
            ];
        }, $_SESSION[$chave]);
    }

    private function listarFeriadosMunicipaisVicentina($ano)
    {
        $feriados = [
            ['date' => sprintf('%04d-05-25', $ano), 'name' => 'Feriado Municipal de Vicentina'],
            ['date' => sprintf('%04d-06-20', $ano), 'name' => 'Aniversário de Vicentina'],
            ['date' => sprintf('%04d-09-12', $ano), 'name' => 'Morte do Padre Roberto'],
            ['date' => sprintf('%04d-10-01', $ano), 'name' => 'Santa Terezinha'],
            ['date' => sprintf('%04d-12-08', $ano), 'name' => 'Morte do Padre José Daniel'],
        ];

        return array_map(function ($feriado) {
            return [
                'date' => $feriado['date'],
                'name' => $feriado['name'],
                'type' => 'municipal'
            ];
        }, $feriados);
    }
}
