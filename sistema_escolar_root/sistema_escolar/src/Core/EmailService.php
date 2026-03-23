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
            $this->mail->Host       = 'smtp.gmail.com';
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'eesjcpi@gmail.com';

            $this->mail->Password   = 'qjrgragcsxgegheo';

            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = 587;
            $this->mail->Timeout    = 60;

            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $this->mail->setFrom('eesjcpi@gmail.com', 'Sistema Escolar');
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
        }
    }

    public function enviar($destinatario, $assunto, $corpo)
    {
        try {
            $this->mail->addAddress($destinatario);
            $this->mail->Subject = $assunto;
            $this->mail->Body    = $corpo;

            return $this->mail->send();
        } catch (Exception $e) {
            $this->mail->clearAddresses();
            return false;
        } finally {
            $this->mail->clearAddresses();
        }
    }
}