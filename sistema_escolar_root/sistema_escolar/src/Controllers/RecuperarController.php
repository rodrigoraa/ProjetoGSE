<?php
require_once ROOT_PATH . '/src/Core/EmailService.php';
require_once ROOT_PATH . '/src/Models/Usuario.php';

class RecuperarController extends Controller
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function index()
    {
        if (isset($_SESSION['usuario_id'])) {
            redirect('/painel');
            exit;
        }
        $this->view('recuperar/esqueci');
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/recuperar');
            exit;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $msg = "";
        $user = $this->usuarioModel->buscarPorEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $pdo = Model::getConexao();
            $pdo->prepare("DELETE FROM recuperacao_senha WHERE email = ?")->execute([$email]);

            $sql = "INSERT INTO recuperacao_senha (email, token, expira_em) VALUES (?, ?, ?)";
            $pdo->prepare($sql)->execute([$email, $tokenHash, $expira]);

            $link = "https://eesjv.com.br/recuperar/senha?token=" . $token;
            $html = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Recuperação de Senha</h2>
                    <p>Olá <strong>" . e($user['nome']) . "</strong>,</p>
                    <p>Recebemos uma solicitação para redefinir sua senha no sistema escolar.</p>
                    <p>Clique no botão abaixo para prosseguir:</p>
                    <p style='margin: 25px 0;'>
                        <a href='$link' style='background: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Criar Nova Senha</a>
                    </p>
                    <p><small>Este link expira em 1 hora. Se você não solicitou isso, ignore este e-mail.</small></p>
                </div>";

            $mail = new EmailService();
            if ($mail->enviar($email, 'Redefinir sua Senha', $html)) {
                $msg = "Instruções enviadas para o seu e-mail.";
            } else {
                $msg = "Erro ao enviar e-mail. Tente novamente mais tarde.";
            }
        } else {
            $msg = "Se o e-mail informado estiver cadastrado, você receberá o link em instantes.";
        }

        $this->view('recuperar/esqueci', ['mensagem' => $msg]);
    }

    public function senha()
    {
        $token = $_GET['token'] ?? '';
        $tokenHash = hash('sha256', $token);

        $pdo = Model::getConexao();
        $stmt = $pdo->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND expira_em > datetime('now', 'localtime')");
        $stmt->execute([$tokenHash]);
        $reg = $stmt->fetch();

        if ($reg) {
            $this->view('recuperar/nova_senha', ['token' => $token, 'email' => $reg['email']]);
        } else {
            $this->view('recuperar/esqueci', ['mensagem' => "Link inválido ou expirado."]);
        }
    }

    public function salvar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        verificar_csrf_token($_POST['csrf_token'] ?? '');

        $token = $_POST['token'] ?? '';
        $tokenHash = hash('sha256', $token);
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (strlen($senha) < 6) {
            die("A senha deve ter no mínimo 6 caracteres.");
        }

        if ($senha !== $confirmar) {
            die("As senhas não coincidem.");
        }

        $pdo = Model::getConexao();
        $stmt = $pdo->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND expira_em > datetime('now', 'localtime')");
        $stmt->execute([$tokenHash]);
        $reg = $stmt->fetch();

        if ($reg) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?")->execute([$hash, $reg['email']]);
            $pdo->prepare("DELETE FROM recuperacao_senha WHERE email = ?")->execute([$reg['email']]);
            registrar_log($pdo, "Seguranca", "Senha alterada via recuperacao para o e-mail: {$reg['email']}");
            redirect('/login?msg=Senha alterada com sucesso!');
        } else {
            die("Sessão de recuperação expirada.");
        }
    }
}
