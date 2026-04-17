<?php
require_once ROOT_PATH . '/src/Models/Agenda.php';

class AgendaController extends Controller
{
    private $agendaModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->agendaModel = new Agenda();
    }
    public function index()
    {
        $avisos = $this->agendaModel->listarProximosAvisos();
        
        $anoAtual = date('Y');
        
        if (!isset($_SESSION["feriados_$anoAtual"])) {
            $jsonFeriados = @file_get_contents("https://brasilapi.com.br/api/feriados/v1/{$anoAtual}");
            
            if ($jsonFeriados) {
                $_SESSION["feriados_$anoAtual"] = json_decode($jsonFeriados, true);
            } else {
                $_SESSION["feriados_$anoAtual"] = []; 
            }
        }

        $feriados = $_SESSION["feriados_$anoAtual"];

        $this->view('agenda/index', [
            'avisos' => $avisos,
            'feriados' => $feriados
        ]);
    }

    public function cadastrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $data_aviso = $_POST['data_aviso'] ?? date('Y-m-d');
            $usuario_id = $_SESSION['usuario_id'];

            if (!empty($titulo) && !empty($data_aviso)) {
                if ($this->agendaModel->adicionar($usuario_id, $data_aviso, $titulo, $descricao)) {
                    registrar_log(
                        Model::getConexao(),
                        'Agenda - Cadastrar',
                        "Criou aviso '{$titulo}' para {$data_aviso}"
                    );
                }
            }

            redirect('/agenda');
            exit;
        }
    }

    public function excluir($id_aviso)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $id_aviso = (int)$id_aviso;
            $usuario_id = $_SESSION['usuario_id'];
            $aviso = $this->agendaModel->buscarPorId($id_aviso);
            $usuario_tipo = $_SESSION['usuario_tipo'] ?? 'comum';

            if ($this->agendaModel->excluir($id_aviso, $usuario_id, $usuario_tipo) && $aviso) {
                registrar_log(
                    Model::getConexao(),
                    'Agenda - Excluir',
                    "Excluiu aviso '{$aviso['titulo']}' (ID: {$id_aviso})"
                );
            }

            redirect('/agenda');
            exit;
        }
    }
}
