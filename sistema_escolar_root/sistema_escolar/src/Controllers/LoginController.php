<?php
require_once ROOT_PATH . '/src/Models/Usuario.php';

class LoginController extends Controller {

    public function index() {
        if (isset($_SESSION['usuario_id'])) {
            redirect('/painel');
            exit;
        }
        $this->view('login');
    }

    public function entrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $usuarioModel = new Usuario();
            $dadosUsuario = $usuarioModel->buscarPorEmail($email);

            if ($dadosUsuario && password_verify($senha, $dadosUsuario['senha'])) {
                $_SESSION['usuario_id'] = $dadosUsuario['id'];
                $_SESSION['usuario_nome'] = $dadosUsuario['nome'];
                $_SESSION['usuario_tipo'] = $dadosUsuario['tipo'];
                
                redirect('/painel');
                exit;
            } else {
                $this->view('login', ['erro' => 'E-mail ou senha incorretos.']);
            }
        }
    }

    public function sair() {
        session_destroy();
        redirect('/login');
        exit;
    }
}