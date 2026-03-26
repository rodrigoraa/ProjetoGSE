<?php
require_once ROOT_PATH . '/src/Lib/PHPMailer/Exception.php';
require_once ROOT_PATH . '/src/Lib/PHPMailer/PHPMailer.php';
require_once ROOT_PATH . '/src/Lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        try {
            $this->mail->SMTPKeepAlive = true;
            $this->mail->Timeout = 60;

            $this->mail->isSMTP();

            $this->mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = $_ENV['MAIL_PORT'] ?? 587;

            $allowSelfSigned = filter_var($_ENV['MAIL_ALLOW_SELF_SIGNED'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($allowSelfSigned) {
                $this->mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }

            $fromEmail = $_ENV['MAIL_FROM'] ?? $_ENV['MAIL_USERNAME'];
            $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'Sistema Escolar';
            $this->mail->setFrom($fromEmail, $fromName);

            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Erro ao configurar PHPMailer: " . $e->getMessage());
        }
    }

    public function enviar($destinatario, $assunto, $corpo)
    {
        try {
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $this->mail->addAddress($destinatario);
            $this->mail->Subject = $assunto;
            $this->mail->Body    = $corpo;

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar email para {$destinatario}: " . $this->mail->ErrorInfo);
            $this->mail->clearAddresses();
            return false;
        } finally {
            $this->mail->clearAddresses();
        }
    }
}
