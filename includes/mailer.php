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
// When set (e.g. on Railway), email is sent over HTTPS via Brevo's API,
// bypassing the blocked SMTP ports. Locally this is empty, so Gmail SMTP is used.
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');

function send_verification_email($toEmail, $toName, $token) {
    $verifyLink = SITE_URL . '/verify.php?token=' . urlencode($token);
    $html       = build_verification_html($toName, $verifyLink);
    $text       = 'Verify your VAR Cars account: ' . $verifyLink;

    // Prefer Brevo's HTTP API when a key is configured (works where SMTP is blocked)
    if (BREVO_API_KEY !== '') {
        return send_via_brevo($toEmail, $toName, 'Verify your VAR Cars account', $html, $text);
    }

    // Fallback: Gmail SMTP (used locally on XAMPP)
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->Timeout    = 10; // fail fast instead of hanging if SMTP is blocked

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your VAR Cars account';
        $mail->Body    = $html;
        $mail->AltBody = $text;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

// Sends a transactional email through Brevo's REST API over HTTPS.
function send_via_brevo($toEmail, $toName, $subject, $html, $text) {
    $payload = array(
        'sender'      => array('email' => MAIL_FROM, 'name' => MAIL_FROM_NAME),
        'to'          => array(array('email' => $toEmail, 'name' => $toName)),
        'subject'     => $subject,
        'htmlContent' => $html,
        'textContent' => $text,
    );

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => array(
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . BREVO_API_KEY,
        ),
    ));

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Brevo returns 201 Created on success
    return $status >= 200 && $status < 300;
}

function build_verification_html($toName, $verifyLink) {
    return '
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
}
