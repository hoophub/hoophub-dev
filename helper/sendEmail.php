<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendWelcomeEmail(string $toEmail, string $username): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hoophub285@gmail.com';
        $mail->Password   = 'fllz kxrm ppwo ypyg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('hoophub285@gmail.com', 'HoopHub');
        $mail->addAddress($toEmail, $username);
        $mail->addReplyTo('hoophub285@gmail.com', 'HoopHub');

        $mail->isHTML(true); 
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = 'base64';
        $mail->Subject = 'Welcome to HoopHub! 🏀';
        $mail->Body    = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;background:#f9f9f9;">
  <div style="background:#1A1A2E;border-radius:12px;padding:32px;text-align:center;">
    <h1 style="color:#E8631A;margin:0;">🏀 HoopHub</h1>
  </div>
  <div style="background:#fff;border-radius:12px;padding:32px;margin-top:16px;">
    <h2 style="color:#1A1A2E;">Welcome, ' . htmlspecialchars($username) . '!</h2>
    <p style="color:#6B7280;">Your account is ready. Start booking courts and finding players.</p>
    <table style="width:100%;margin:20px 0;border-collapse:collapse;">
      <tr><td style="padding:8px 0;font-weight:bold;color:#374151;">Username:</td><td style="color:#6B7280;">' . htmlspecialchars($username) . '</td></tr>
      <tr><td style="padding:8px 0;font-weight:bold;color:#374151;">Email:</td><td style="color:#6B7280;">' . htmlspecialchars($toEmail) . '</td></tr>
    </table>
    <a href="http://localhost/BASKETBALLERS/dashboard.php"
       style="display:inline-block;background:#E8631A;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:8px;">
      Go to Dashboard →
    </a>
  </div>
  <p style="color:#9CA3AF;font-size:12px;text-align:center;margin-top:16px;">HoopHub · You are receiving this because you just registered.</p>
</body>
</html>';
        $mail->AltBody = "Welcome to HoopHub, !\nEmail: {$toEmail}\nLogin at http://localhost/BASKETBALLERS/dashboard.php";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('HoopHub mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}