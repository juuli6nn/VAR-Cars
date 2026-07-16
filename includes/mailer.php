<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}
define('MAIL_HOST',     getenv('MAIL_HOST')     ?: 'smtp.gmail.com');
define('MAIL_PORT',     (int)(getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: (defined('LOCAL_MAIL_USERNAME') ? LOCAL_MAIL_USERNAME : ''));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: (defined('LOCAL_MAIL_PASSWORD') ? LOCAL_MAIL_PASSWORD : ''));
define('MAIL_FROM',     MAIL_USERNAME);
define('MAIL_FROM_NAME','VAR Cars');
define('SITE_URL',      getenv('SITE_URL') ?: 'http://localhost/VAR-Cars/public');

function send_verification_email($toEmail, $toName, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $verifyLink = SITE_URL . '/verify.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your VAR Cars account';
        $mail->Body    = '
<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;background:#0e1b22;color:#fff;padding:2rem;">
    <div style="max-width:480px;margin:0 auto;background:#1b2b34;border-radius:12px;padding:2rem;">
        <h2 style="color:#9aa4a7;margin-top:0;">Welcome to VAR Cars, ' . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . '!</h2>
        <p>Click the button below to verify your email address and activate your account.</p>
        <a href="' . $verifyLink . '"
           style="display:inline-block;padding:0.75rem 1.5rem;background:#9aa4a7;color:#0e1b22;
                  border-radius:8px;text-decoration:none;font-weight:700;margin:1rem 0;">
            Verify Email
        </a>
        <p style="color:#555;font-size:0.85rem;">
            Or copy this link:<br>
            <a href="' . $verifyLink . '" style="color:#9aa4a7;">' . $verifyLink . '</a>
        </p>
        <p style="color:#555;font-size:0.8rem;margin-bottom:0;">
            If you did not create an account, you can ignore this email.
        </p>
    </div>
</body>
</html>';
        $mail->AltBody = 'Verify your VAR Cars account: ' . $verifyLink;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
