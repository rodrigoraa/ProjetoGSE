<?php
require_once ROOT_PATH . '/src/Models/Usuario.php';

class UsuarioController extends Controller
{
    public function index()
    {
        $this->verificarAdmin();

        $usuarioModel = new Usuario();
        $lista = $usuarioModel->listar();

        $sucesso = $_SESSION['msg_sucesso'] ?? null;
        $erro = $_SESSION['msg_erro'] ?? null;

        unset($_SESSION['msg_sucesso'], $_SESSION['msg_erro']);

        $this->view('usuarios/index', [
            'funcionarios' => $lista,
            'msg_sucesso' => $sucesso,
            'msg_erro' => $erro
        ]);
    }

    public function cadastrar()
    {
        $this->verificarAdmin();
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $usuarioModel = new Usuario();
            if ($usuarioModel->cadastrar($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['tipo'])) {
                registrar_log(Model::getConexao(), "Usuario - Criar", "Criou: " . $_POST['nome']);

                $_SESSION['msg_sucesso'] = "Usuario cadastrado com sucesso!";
                redirect('/usuario');
                exit;
            }

            $mensagem = '<p class="error-message">Erro: e-mail ja cadastrado?</p>';
        }
        $this->view('usuarios/cadastrar', ['mensagem' => $mensagem]);
    }

    public function editar($id)
    {
        $this->verificarAdmin();
        if (!$id) {
            redirect('/usuario');
            exit;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->buscarPorId($id);
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            $novaSenha = !empty($_POST['senha']) ? $_POST['senha'] : null;

            if ($usuarioModel->atualizar($id, $_POST['nome'], $_POST['email'], $_POST['tipo'], $novaSenha)) {
                registrar_log(Model::getConexao(), "Usuario - Editar", "Editou ID: $id");

                $_SESSION['msg_sucesso'] = "Usuario atualizado com sucesso!";
                redirect('/usuario');
                exit;
            }

            $mensagem = '<p class="error-message">Erro ao atualizar.</p>';
        }
        $this->view('usuarios/editar', ['user' => $user, 'mensagem' => $mensagem]);
    }

    public function excluir($id)
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/usuario');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        if ($id == $_SESSION['usuario_id']) {
            $_SESSION['msg_erro'] = "Voce nao pode excluir sua propria conta.";
            redirect('/usuario');
            exit;
        }

        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->excluir($id);

        if ($resultado === true) {
            registrar_log(Model::getConexao(), "Usuario - Excluir", "Excluiu ID: $id");
            $_SESSION['msg_sucesso'] = "Usuario excluido com sucesso!";
        } elseif ($resultado === 'tem_registros') {
            $_SESSION['msg_erro'] = "Nao e possivel excluir este funcionario pois ele possui registros vinculados.";
        } else {
            $_SESSION['msg_erro'] = "Erro desconhecido ao tentar excluir.";
        }

        redirect('/usuario');
        exit;
    }

    public function perfil()
    {
        if (!isset($_SESSION['usuario_id'])) {
            redirect('/login');
            exit;
        }

        $usuarioModel = new Usuario();
        $id = $_SESSION['usuario_id'];
        $user = $usuarioModel->buscarPorId($id);
        $mensagem = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_csrf_token($_POST['csrf_token'] ?? '');

            if (password_verify($_POST['senha_atual'], $user['senha'])) {
                $novaSenha = !empty($_POST['nova_senha']) ? $_POST['nova_senha'] : null;

                if ($usuarioModel->atualizarPerfil($id, $_POST['nome'], $_POST['email'], $novaSenha)) {
                    $_SESSION['usuario_nome'] = $_POST['nome'];
                    $mensagem = '<p class="success-message">Perfil atualizado com sucesso!</p>';
                    $user = $usuarioModel->buscarPorId($id);
                }
            } else {
                $mensagem = '<p class="error-message">Senha atual incorreta.</p>';
            }
        }

        $this->view('usuarios/perfil', ['user' => $user, 'mensagem' => $mensagem]);
    }

    private function verificarAdmin()
    {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
            redirect('/painel');
            exit;
        }
    }
}
