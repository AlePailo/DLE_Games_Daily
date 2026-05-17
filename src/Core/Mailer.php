<?php declare(strict_types = 1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int) $_ENV['MAIL_PORT'];
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendVerification(string $toEmail, string $rawToken) : void {
        $link = sprintf(
            'http://%s%s/verify?token=%s',
            $_SERVER['HTTP_HOST'],
            $_ENV['APP_BASE_PATH'],
            urlencode($rawToken)
        );

        $this->mailer->clearAddresses();
        $this->mailer->addAddress($toEmail);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Verify your account - DLE Games Daily';
        $this->mailer->Body = "<h2>Welcome to DLE Games Daily!</h2>
            <p>Click the link to verify your account:</p>
            <p><a href='{$link}'>{$link}</a></p>
            <p>Link will expire 24 hours after verification email sending.</p>
        ";
        $this->mailer->AltBody = "Verify your account: {$link}";

        $this->mailer->send();
    }
}