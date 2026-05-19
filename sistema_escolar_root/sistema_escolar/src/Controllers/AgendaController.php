<?php
require_once ROOT_PATH . '/src/Models/Agenda.php';
require_once ROOT_PATH . '/src/Services/CalendarioEscolarImportador.php';
require_once ROOT_PATH . '/src/Services/FeriadosService.php';

class AgendaController extends Controller
{
    private $agendaModel;
    private $feriadosService;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
        $this->agendaModel = new Agenda();
        $this->feriadosService = new FeriadosService();
    }
    public function index()
    {
        $avisos = $this->agendaModel->listarProximosAvisos();
        $feriados = $this->feriadosService->listarPorAno((int)date('Y'));

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

    public function importarCalendario()
    {
        $this->verificarAdmin();
        $resultado = null;
        $eventos = $_SESSION['calendario_importacao_eventos'] ?? [];
        $ano = (int)($_POST['ano'] ?? $_GET['ano'] ?? date('Y'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');
            $acao = $_POST['acao'] ?? 'analisar';

            if ($acao === 'salvar') {
                $this->salvarEventosImportados();
            }

            $importador = new CalendarioEscolarImportador();
            $resultado = $importador->importar(
                $_FILES['calendario_pdf'] ?? [],
                $ano,
                $_POST['texto_calendario'] ?? ''
            );

            $eventos = $resultado['eventos'] ?? [];
            $_SESSION['calendario_importacao_eventos'] = $eventos;
        }

        $this->view('agenda/importar_calendario', [
            'resultado' => $resultado,
            'eventos' => $eventos,
            'ano' => $ano
        ]);
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

    private function salvarEventosImportados()
    {
        $selecionados = $_POST['eventos'] ?? [];
        $eventos = $_SESSION['calendario_importacao_eventos'] ?? [];

        if (empty($selecionados) || empty($eventos)) {
            definir_flash('aviso', 'Nenhum item selecionado', 'Marque pelo menos um item importado antes de salvar.');
            redirect('/agenda/importarCalendario');
            exit;
        }

        $salvos = 0;
        $ignorados = 0;
        $usuario_id = $_SESSION['usuario_id'];

        foreach ($selecionados as $indice) {
            $indice = (int)$indice;
            $evento = $eventos[$indice] ?? null;

            if (!$this->eventoImportadoValido($evento)) {
                $ignorados++;
                continue;
            }

            $titulo = '[Calendário] ' . trim($evento['titulo']);
            $descricao = trim(($evento['tipo'] ?? 'Evento') . "\n" . ($evento['descricao'] ?? ''));

            if ($this->agendaModel->existePorDataTitulo($evento['data'], $titulo)) {
                $ignorados++;
                continue;
            }

            if ($this->agendaModel->adicionar($usuario_id, $evento['data'], $titulo, $descricao)) {
                $salvos++;
            } else {
                $ignorados++;
            }
        }

        unset($_SESSION['calendario_importacao_eventos']);

        definir_flash(
            $salvos > 0 ? 'sucesso' : 'aviso',
            $salvos > 0 ? 'Calendário importado' : 'Nenhum item novo foi salvo',
            "Itens salvos: {$salvos}. Itens ignorados: {$ignorados}."
        );
        registrar_log(Model::getConexao(), 'Agenda - Importar calendário', "Salvou {$salvos} itens do calendário escolar.");

        redirect('/agenda');
        exit;
    }

    private function eventoImportadoValido($evento)
    {
        return is_array($evento)
            && !empty($evento['data'])
            && !empty($evento['titulo'])
            && DateTimeImmutable::createFromFormat('Y-m-d', $evento['data']) !== false;
    }

    private function verificarAdmin()
    {
        if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
            definir_flash('erro', 'Acesso restrito', 'Somente administradores podem importar calendário escolar.');
            redirect('/agenda');
            exit;
        }
    }
}
