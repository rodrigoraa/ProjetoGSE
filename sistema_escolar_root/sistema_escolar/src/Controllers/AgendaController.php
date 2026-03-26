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

        $this->view('agenda/index', ['avisos' => $avisos]);
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
                $this->agendaModel->adicionar($usuario_id, $data_aviso, $titulo, $descricao);
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

            $usuario_tipo = $_SESSION['usuario_tipo'] ?? 'comum';

            $this->agendaModel->excluir($id_aviso, $usuario_id, $usuario_tipo);

            redirect('/agenda');
            exit;
        }
    }
}
