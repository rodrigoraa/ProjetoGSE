<?php
require_once ROOT_PATH . '/src/Models/Aluno.php';

class AlunoController extends Controller
{
    private function autenticar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }
    }

    public function index()
    {
        $this->autenticar();

        $termo = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
        if ($pagina < 1) {
            $pagina = 1;
        }

        $limite = 15;
        $inicio = ($pagina - 1) * $limite;

        $alunoModel = new Aluno();
        $total_registros = $alunoModel->contarTotal($termo);
        $total_paginas = ceil($total_registros / $limite);
        $lista_alunos = $alunoModel->listar($limite, $inicio, $termo);

        $this->view('alunos/index', [
            'lista_alunos' => $lista_alunos,
            'termo' => $termo,
            'pagina_atual' => $pagina,
            'total_paginas' => $total_paginas,
            'total_registros' => $total_registros
        ]);
    }

    public function cadastrar()
    {
        $this->autenticar();

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

            $nome = strip_tags(trim($_POST['nome']));
            $dataNasc = $_POST['data_nascimento'];
            $idTurma = filter_input(INPUT_POST, 'id_turma', FILTER_VALIDATE_INT);
            $dataDva = $_POST['data_dva'] ?: null;
            $tel_aluno = strip_tags(trim($_POST['telefone_aluno'] ?? ''));
            $tel_responsavel = strip_tags(trim($_POST['telefone_responsavel'] ?? ''));

            $dados_form = $_POST;

            if (empty($nome) || empty($dataNasc) || !$idTurma) {
                $mensagem = alerta_html(
                    'erro',
                    'Faltam dados obrigatórios',
                    'Para cadastrar o aluno, informe nome completo, data de nascimento e turma.',
                    'Revise os campos do formulário e tente novamente.'
                );
            } elseif ($alunoModel->existeAluno($nome, $dataNasc)) {
                $mensagem = alerta_html(
                    'aviso',
                    'Possível cadastro duplicado',
                    'Já existe um aluno cadastrado com este nome e esta data de nascimento.',
                    'Confirme se o aluno já está na lista antes de criar um novo registro.'
                );
            } else {
                $idNovo = $alunoModel->cadastrar($nome, $dataNasc, $idTurma, $dataDva, $tel_aluno, $tel_responsavel);

                if ($idNovo) {
                    registrar_log(Model::getConexao(), "Cadastrar Aluno", "Novo aluno: $nome (ID: $idNovo)");
                    definir_flash(
                        'sucesso',
                        'Aluno cadastrado com sucesso',
                        "O cadastro de $nome foi salvo com sucesso.",
                        'Você já pode abrir o perfil do aluno para conferir os dados ou continuar cadastrando outros.'
                    );
                    redirect('/aluno');
                    exit;
                }

                $mensagem = alerta_html(
                    'erro',
                    'Não foi possível salvar o cadastro',
                    'O sistema não conseguiu gravar o novo aluno neste momento.',
                    'Tente novamente em alguns instantes. Se o erro continuar, avise o suporte com o nome do aluno que tentou cadastrar.'
                );
            }
        }

        $this->view('alunos/cadastrar', [
            'turmas' => $alunoModel->getTurmas(),
            'mensagem' => $mensagem,
            'd' => $dados_form
        ]);
    }

    public function editar($id)
    {
        $this->autenticar();

        $alunoModel = new Aluno();
        $aluno = $alunoModel->buscarPorId($id);

        if (!$aluno) {
            definir_flash('erro', 'Aluno não encontrado', 'O registro que você tenta editar não existe.');
            redirect('/aluno');
            exit;
        }

        $mensagem = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $nome = strip_tags(trim($_POST['nome']));
            $dataNasc = $_POST['data_nascimento'];
            $idTurma = filter_input(INPUT_POST, 'id_turma', FILTER_VALIDATE_INT);
            $dataDva = $_POST['data_dva'] ?: null;
            $tel_aluno = strip_tags(trim($_POST['telefone_aluno'] ?? ''));
            $tel_responsavel = strip_tags(trim($_POST['telefone_responsavel'] ?? ''));

            if (empty($nome) || empty($dataNasc) || !$idTurma) {
                $mensagem = alerta_html('erro', 'Campos obrigatórios', 'Preencha nome, data de nascimento e turma.');
            } else {
                $sucesso = $alunoModel->atualizar($id, $nome, $dataNasc, $idTurma, $dataDva, $tel_aluno, $tel_responsavel);

                if ($sucesso) {
                    registrar_log(Model::getConexao(), "Editar Aluno", "Editou: $nome (ID: $id)");
                    definir_flash('sucesso', 'Alterações salvas', "Os dados de $nome foram atualizados.");
                    redirect('/aluno');
                    exit;
                }
                $mensagem = alerta_html('erro', 'Erro ao salvar', 'Não foi possível atualizar os dados no banco.');
            }
        }

        $this->view('alunos/editar', [
            'aluno' => $aluno,
            'turmas' => $alunoModel->getTurmas(),
            'mensagem' => $mensagem
        ]);
    }
    public function excluir($id)
    {
        $this->autenticar();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/aluno');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($_SESSION['usuario_tipo'] !== 'admin') {
            definir_flash(
                'erro',
                'Você não tem permissão para apagar este aluno',
                'Somente administradores podem excluir registros de alunos.',
                'Se essa exclusão for necessária, peça apoio a um usuário administrador.'
            );
            redirect('/aluno');
            exit;
        }

        if ($id) {
            $alunoModel = new Aluno();
            $nomeExcluido = $alunoModel->excluir($id);

            if ($nomeExcluido) {
                registrar_log(Model::getConexao(), "Apagar Aluno", "Excluiu: $nomeExcluido (ID: $id)");
                definir_flash(
                    'sucesso',
                    'Aluno removido com sucesso',
                    "O registro de $nomeExcluido foi apagado.",
                    'Se a exclusão foi indevida, será necessário cadastrar o aluno novamente.'
                );
            }
        }

        redirect('/aluno');
        exit;
    }

    public function perfil($id)
    {
        $this->autenticar();

        $alunoModel = new Aluno();
        $aluno = $alunoModel->buscarPorId($id);

        if (!$aluno) {
            definir_flash(
                'erro',
                'Aluno não encontrado',
                'Não localizamos o aluno solicitado.',
                'Ele pode ter sido removido ou o link acessado não é mais válido.'
            );
            redirect('/aluno');
            exit;
        }

        $status_dva = 'sem_dva';
        $dias_restantes = 0;

        if (!empty($aluno['data_vencimento'])) {
            $hoje = new DateTime('today');
            $venc = new DateTime($aluno['data_vencimento']);
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
}
