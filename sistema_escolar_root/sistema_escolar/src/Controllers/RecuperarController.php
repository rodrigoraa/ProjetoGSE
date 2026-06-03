<?php
require_once ROOT_PATH . '/src/Core/EmailService.php';
require_once ROOT_PATH . '/src/Models/Usuario.php';

class RecuperarController extends Controller
{
    private $usuarioModel;
    private const MSG_ENVIO = 'Se o e-mail informado estiver cadastrado, você receberá o link em instantes.';

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
        $msg = self::MSG_ENVIO;
        $user = $this->usuarioModel->buscarPorEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $pdo = Model::getConexao();
            $pdo->prepare("DELETE FROM recuperacao_senha WHERE email = ?")->execute([$email]);

            $sql = "INSERT INTO recuperacao_senha (email, token, expira_em) VALUES (?, ?, ?)";
            $pdo->prepare($sql)->execute([$email, $tokenHash, $expira]);

            $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            $link = $baseUrl . "/recuperar/senha?token=" . $token;
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
            if (!$mail->enviar($email, 'Redefinir sua Senha', $html)) {
                error_log("Falha ao enviar e-mail de recuperacao para {$email}.");
            }
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
            $this->view('recuperar/nova_senha', ['token' => $token, 'email' => $reg['email'], 'mensagem' => '']);
        } else {
            $this->view('recuperar/esqueci', ['mensagem' => "Link inválido ou expirado. Solicite uma nova recuperação."]);
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
        $pdo = Model::getConexao();
        $stmt = $pdo->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND expira_em > datetime('now', 'localtime')");
        $stmt->execute([$tokenHash]);
        $reg = $stmt->fetch();
        $email = $reg['email'] ?? '';

        if (strlen($senha) < 6) {
            $this->view('recuperar/nova_senha', [
                'token' => $token,
                'email' => $email,
                'mensagem' => 'A senha deve ter no mínimo 6 caracteres.'
            ]);
            return;
        }

        if ($senha !== $confirmar) {
            $this->view('recuperar/nova_senha', [
                'token' => $token,
                'email' => $email,
                'mensagem' => 'As senhas não coincidem.'
            ]);
            return;
        }

        if ($reg) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?")->execute([$hash, $reg['email']]);
            $pdo->prepare("DELETE FROM recuperacao_senha WHERE email = ?")->execute([$reg['email']]);
            registrar_log($pdo, "Seguranca", "Senha alterada via recuperacao para o e-mail: {$reg['email']}");
            redirect('/login?msg=Senha alterada com sucesso!');
            exit;
        } else {
            $this->view('recuperar/esqueci', ['mensagem' => 'Sessão de recuperação expirada. Solicite um novo link.']);
        }
    }
}
