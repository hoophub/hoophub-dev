<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// ── DB ──────────────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host=localhost;dbname=hoophub;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Location: register.php?error=" . urlencode("Database error!"));
    exit();
}

// ── Guard ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// ── Inputs ───────────────────────────────────────────────────────────
$username         = trim($_POST['username']         ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';

// ── Validation ───────────────────────────────────────────────────────
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    header("Location: register.php?error=" . urlencode("Please fill in all fields!"));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=" . urlencode("Invalid email address!"));
    exit();
}

if (strlen($password) < 6) {
    header("Location: register.php?error=" . urlencode("Password must be at least 6 characters!"));
    exit();
}

if ($password !== $confirm_password) {
    header("Location: register.php?error=password_mismatch");
    exit();
}

// ── Duplicate check ──────────────────────────────────────────────────
$check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$check->execute([$email, $username]);
if ($check->fetch()) {
    header("Location: register.php?error=email_exists");
    exit();
}

// ── Insert ───────────────────────────────────────────────────────────
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");

if (!$stmt->execute([$username, $email, $hash])) {
    header("Location: register.php?error=" . urlencode("Registration failed! Please try again."));
    exit();
}

// ── Welcome email ─────────────────────────────────────────────────────
sendWelcomeEmail($email, $username);

header("Location: login.php?success=" . urlencode("Registration successful! Welcome email sent."));
exit();


// ── Mailer function ───────────────────────────────────────────────────
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
  <p style="color:#9CA3AF;font-size:12px;text-align:center;margin-top:16px;">HoopHub · You received this because you just registered.</p>
</body>
</html>';
        $mail->AltBody = "Welcome to HoopHub, {$username}!\nEmail: {$toEmail}\nLogin at http://localhost/BASKETBALLERS/dashboard.php";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('HoopHub mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}