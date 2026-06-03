<?php
require_once ROOT_PATH . '/src/Models/Usuario.php';

class LoginController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['usuario_id'])) {
            redirect('/painel');
            exit;
        }

        $erro = $_SESSION['mensagem_erro'] ?? null;
        unset($_SESSION['mensagem_erro']);
        $mensagem = trim((string)($_GET['msg'] ?? ''));
        if ($mensagem === '') {
            $mensagem = null;
        }

        $this->view('login', ['erro' => $erro, 'mensagem' => $mensagem]);
    }

    public function entrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'] ?? '';

        $usuarioModel = new Usuario();
        $dadosUsuario = $usuarioModel->buscarPorEmail($email);

        if ($dadosUsuario && password_verify($senha, $dadosUsuario['senha'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $dadosUsuario['id'];
            $_SESSION['usuario_nome'] = $dadosUsuario['nome'];
            $_SESSION['usuario_tipo'] = $dadosUsuario['tipo'];
            $_SESSION['last_activity'] = time();

            registrar_log(Model::getConexao(), 'Login', "Usuário {$dadosUsuario['nome']} acessou o sistema.");

            redirect('/painel');
            exit;
        }

        registrar_log(Model::getConexao(), 'Login - Falha', "Tentativa de login inválida para o e-mail: $email");
        usleep(300000);
        $this->view('login', ['erro' => 'E-mail ou senha incorretos.']);
    }

    public function sair()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        redirect('/login');
        exit;
    }
}
