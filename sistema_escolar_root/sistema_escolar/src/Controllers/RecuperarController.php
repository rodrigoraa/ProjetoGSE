<?php
require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Core/EmailService.php';
require_once ROOT_PATH . '/src/Models/Usuario.php'; 

class RecuperarController extends Controller {

    public function index() {
        $this->view('recuperar/esqueci');
    }

    public function enviar() {
        $email = $_POST['email'] ?? '';
        $msg = '';

        $pdo = Model::getConexao();
        
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $sql = "INSERT INTO recuperacao_senha (email, token, expira_em) VALUES (?, ?, ?)";
            $pdo->prepare($sql)->execute([$email, $token, $expira]);

            $link = "https://eesjv.com.br/recuperar/senha?token=" . $token;
            $html = "<h2>Recuperação de Senha</h2>
                     <p>Olá {$user['nome']},</p>
                     <p>Clique no link abaixo para criar uma nova senha:</p>
                     <p><a href='$link'>$link</a></p>
                     <p>Este link expira em 1 hora.</p>";

            $mail = new EmailService();
            if ($mail->enviar($email, 'Redefinir Senha', $html)) {
                $msg = "<span style='color:green'>✅ E-mail enviado! Verifique sua caixa de entrada.</span>";
            } else {
                $msg = "<span style='color:red'>❌ Erro ao enviar e-mail. Contate o suporte.</span>";
            }
        } else {
            $msg = "<span style='color:green'>✅ Se o e-mail existir, enviamos um link.</span>";
        }

        $this->view('recuperar/esqueci', ['mensagem' => $msg]);
    }

    public function senha() {
        $token = $_GET['token'] ?? '';
        
        $pdo = Model::getConexao();
        
        $stmt = $pdo->prepare("SELECT email FROM recuperacao_senha WHERE token = ? AND expira_em > datetime('now')");
        $stmt->execute([$token]);
        $reg = $stmt->fetch();

        if ($reg) {
            $this->view('recuperar/nova_senha', ['token' => $token, 'email' => $reg['email']]);
        } else {
            die("<h1>Link inválido ou expirado.</h1><a href='/recuperar'>Tentar novamente</a>");
        }
    }

    public function salvar() {
        $token = $_POST['token'];
        $senha = $_POST['senha'];
        
        $pdo = Model::getConexao();
        
        $stmt = $pdo->prepare("SELECT email FROM recuperacao_senha WHERE token = ?");
        $stmt->execute([$token]);
        $reg = $stmt->fetch();

        if ($reg) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET senha = ? WHERE email = ?")->execute([$hash, $reg['email']]);
            
            $pdo->prepare("DELETE FROM recuperacao_senha WHERE token = ?")->execute([$token]);

            redirect('/login?msg=Senha alterada com sucesso!');
        } else {
            echo "Erro ao alterar senha.";
        }
    }
}