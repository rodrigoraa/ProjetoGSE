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

        $feriados = array_merge(
            $_SESSION["feriados_$anoAtual"],
            $this->listarFeriadosMunicipaisVicentina((int)$anoAtual)
        );

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
                if ($this->agendaModel->existeDuplicadoRecente($usuario_id, $data_aviso, $titulo, $descricao)) {
                    definir_flash(
                        'aviso',
                        'Aviso já registrado',
                        'Um lembrete igual foi enviado agora há pouco e não foi cadastrado novamente.'
                    );
                } elseif ($this->agendaModel->adicionar($usuario_id, $data_aviso, $titulo, $descricao)) {
                    registrar_log(
                        Model::getConexao(),
                        'Agenda - Cadastrar',
                        "Criou aviso '{$titulo}' para {$data_aviso}"
                    );

                    definir_flash(
                        'sucesso',
                        'Aviso cadastrado com sucesso',
                        'O lembrete foi adicionado à agenda.'
                    );
                }
            }

            redirect('/agenda');
            exit;
        }
    }

    public function editar($id_aviso)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $id_aviso = (int)$id_aviso;
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $data_aviso = $_POST['data_aviso'] ?? date('Y-m-d');
            $usuario_id = $_SESSION['usuario_id'];
            $usuario_tipo = $_SESSION['usuario_tipo'] ?? 'comum';
            $aviso = $this->agendaModel->buscarPorId($id_aviso);

            if (!$aviso) {
                definir_flash('erro', 'Aviso não encontrado', 'O lembrete selecionado não existe mais.');
                redirect('/agenda');
                exit;
            }

            $is_admin = $usuario_tipo === 'admin';
            $is_dono = (int)$aviso['usuario_id'] === (int)$usuario_id;

            if (!$is_admin && !$is_dono) {
                definir_flash('erro', 'Ação não permitida', 'Você só pode editar avisos cadastrados por você.');
                redirect('/agenda');
                exit;
            }

            if (empty($titulo) || empty($data_aviso)) {
                definir_flash('erro', 'Dados incompletos', 'Informe a data e o título do aviso antes de salvar.');
                redirect('/agenda');
                exit;
            }

            if ($this->agendaModel->atualizar($id_aviso, $usuario_id, $usuario_tipo, $data_aviso, $titulo, $descricao)) {
                registrar_log(
                    Model::getConexao(),
                    'Agenda - Editar',
                    "Editou aviso '{$titulo}' (ID: {$id_aviso})"
                );

                definir_flash(
                    'sucesso',
                    'Aviso atualizado com sucesso',
                    'As alterações do lembrete foram salvas.'
                );
            } else {
                definir_flash(
                    'aviso',
                    'Nenhuma alteração salva',
                    'O aviso já estava com esses dados ou não pôde ser atualizado.'
                );
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
