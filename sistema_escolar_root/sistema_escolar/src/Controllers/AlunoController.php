<?php
require_once ROOT_PATH . '/src/Models/Aluno.php';

class AlunoController extends Controller
{

    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $termo = $_GET['busca'] ?? '';
        $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
        $limite = 15;
        $inicio = ($pagina * $limite) - $limite;

        $alunoModel = new Aluno();
        $total_registros = $alunoModel->contarTotal($termo);
        $total_paginas = ceil($total_registros / $limite);

        $lista_alunos = $alunoModel->listar($limite, $inicio, $termo);

        $dados = [
            'lista_alunos' => $lista_alunos,
            'termo' => $termo,
            'pagina_atual' => $pagina,
            'total_paginas' => $total_paginas,
            'total_registros' => $total_registros
        ];

        $this->view('alunos/index', $dados);
    }

    public function cadastrar()
    {
        $alunoModel = new Aluno();
        $mensagem = '';
        $dados_form = [
            'nome' => '',
            'data_nascimento' => '',
            'data_dva' => '',
            'id_turma' => '',
            'telefone_aluno' => '',
            'telefone_responsavel' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = trim($_POST['nome']);
            $dataNasc = $_POST['data_nascimento'];
            $idTurma = $_POST['id_turma'];
            $dataDva = $_POST['data_dva'];

            $tel_aluno = $_POST['telefone_aluno'] ?? null;
            $tel_responsavel = $_POST['telefone_responsavel'] ?? null;

            $dados_form = $_POST;

            if ($alunoModel->existeAluno($nome, $dataNasc)) {
                $mensagem = '<p class="error-message">🚫 Erro: Aluno já cadastrado com este nome e data.</p>';
            } else {
                $idNovo = $alunoModel->cadastrar($nome, $dataNasc, $idTurma, $dataDva, $tel_aluno, $tel_responsavel);

                if ($idNovo) {
                    registrar_log(Model::getConexao(), "Cadastrar Aluno", "Novo aluno: $nome (ID: $idNovo)");
                    redirect('/aluno');
                    exit;
                } else {
                    $mensagem = '<p class="error-message">Erro técnico ao salvar no banco.</p>';
                }
            }
        }

        $turmas = $alunoModel->getTurmas();

        $this->view('alunos/cadastrar', [
            'turmas' => $turmas,
            'mensagem' => $mensagem,
            'd' => $dados_form
        ]);
    }

    public function editar($id)
    {
        if (!$id) {
            redirect('/aluno');
            exit;
        }

        $alunoModel = new Aluno();
        $mensagem = '';

        $dados_aluno = $alunoModel->buscarPorId($id);

        if (!$dados_aluno) {
            die("Aluno não encontrado.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = trim($_POST['nome']);
            $dataNasc = $_POST['data_nascimento'];
            $idTurma = $_POST['id_turma'];
            $dataDva = $_POST['data_dva'];

            $tel_aluno = $_POST['telefone_aluno'] ?? null;
            $tel_responsavel = $_POST['telefone_responsavel'] ?? null;

            if ($alunoModel->atualizar($id, $nome, $dataNasc, $idTurma, $dataDva, $tel_aluno, $tel_responsavel)) {
                registrar_log(Model::getConexao(), "Editar Aluno", "Editou ID: $id - Nome: $nome");
                redirect('/aluno');
                exit;
            } else {
                $mensagem = '<p class="error-message">Erro ao atualizar.</p>';
            }
        }

        $turmas = $alunoModel->getTurmas();

        $this->view('alunos/editar', [
            'aluno' => $dados_aluno,
            'turmas' => $turmas,
            'mensagem' => $mensagem
        ]);
    }

    public function excluir($id)
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        if ($id) {
            $alunoModel = new Aluno();
            $nomeExcluido = $alunoModel->excluir($id);

            if ($nomeExcluido) {
                registrar_log(Model::getConexao(), "Apagar Aluno", "Excluiu: $nomeExcluido (ID: $id)");
            }
        }

        redirect('/aluno');
        exit;
    }

    public function perfil($id)
    {
        if (!$id) {
            redirect('/aluno');
            exit;
        }

        $alunoModel = new Aluno();
        $aluno = $alunoModel->buscarPorId($id);

        if (!$aluno) {
            die("Aluno não encontrado.");
        }

        $status_dva = 'sem_dva';
        $dias_restantes = 0;

        if (!empty($aluno['data_dva'])) {
            $hoje = new DateTime();
            $venc = new DateTime($aluno['data_dva']);
            $diff = $hoje->diff($venc);
            $dias = (int)$diff->format("%r%a");

            if ($dias < 0) {
                $status_dva = 'vencida';
            } elseif ($dias <= 30) {
                $status_dva = 'avencer';
            } else {
                $status_dva = 'vigente';
            }
            $dias_restantes = $dias;
        }

        $this->view('alunos/perfil', [
            'aluno' => $aluno,
            'status' => $status_dva,
            'dias' => $dias_restantes
        ]);
    }

    public function pendentes()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $alunoModel = new Aluno();
        $lista = $alunoModel->listarSemDva();

        $this->view('alunos/pendentes', [
            'lista_alunos' => $lista
        ]);
    }
}
