<?php
session_start();
require_once 'db.php';

// ── Redirect if already logged in ───────────────────────────
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// ── Sanitize helpers ─────────────────────────────────────────
function clean(string $v): string {
    return trim(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));
}

function redirect(string $err, array $back = []): never {
    $qs = http_build_query(array_merge(['error' => $err], $back));
    header("Location: register.php?{$qs}");
    exit();
}

// ── Collect inputs ───────────────────────────────────────────
$first_name      = clean($_POST['first_name']      ?? '');
$last_name       = clean($_POST['last_name']       ?? '');
$date_of_birth   = clean($_POST['date_of_birth']   ?? '');
$email           = strtolower(clean($_POST['email']          ?? ''));
$phone           = clean($_POST['phone']           ?? '');
$service_address = clean($_POST['service_address'] ?? '');
$username        = clean($_POST['username']        ?? '');
$password        = $_POST['password']        ?? '';   // raw – will be hashed
$confirm_password = $_POST['confirm_password'] ?? '';

// ── Carry-back values (never include passwords) ───────────────
$back = [
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'dob'        => $date_of_birth,
    'email'      => $email,
    'phone'      => $phone,
    'address'    => $service_address,
    'username'   => $username,
];

// ── Required field check ─────────────────────────────────────
$required = compact('first_name','last_name','date_of_birth','email','phone','service_address','username','password','confirm_password');
foreach ($required as $field => $val) {
    if ($val === '') redirect('All fields are required.', $back);
}

// ── Name length ───────────────────────────────────────────────
if (strlen($first_name) > 50) redirect('First name must be 50 characters or fewer.', $back);
if (strlen($last_name)  > 50) redirect('Last name must be 50 characters or fewer.',  $back);

// ── Name format (letters, spaces, hyphens, apostrophes) ───────
if (!preg_match("/^[A-Za-z\s\-'\.]+$/", $first_name)) redirect('First name contains invalid characters.', $back);
if (!preg_match("/^[A-Za-z\s\-'\.]+$/", $last_name))  redirect('Last name contains invalid characters.',  $back);

// ── Date of birth ─────────────────────────────────────────────
$dob = DateTime::createFromFormat('Y-m-d', $date_of_birth);
if (!$dob || $dob->format('Y-m-d') !== $date_of_birth) {
    redirect('invalid_dob', $back);
}
$age = (new DateTime())->diff($dob)->y;
if ($age < 13) redirect('You must be at least 13 years old to register.', $back);
if ($age > 120) redirect('invalid_dob', $back);

// ── Email format ──────────────────────────────────────────────
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) redirect('Please enter a valid email address.', $back);

// ── Phone format ──────────────────────────────────────────────
if (!preg_match('/^[0-9+\-\s\(\)]{7,20}$/', $phone)) redirect('Please enter a valid phone number.', $back);

// ── Username ──────────────────────────────────────────────────
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    redirect('Username must be 3–20 characters (letters, numbers, underscores only).', $back);
}

// ── Password strength (must match JS rules) ───────────────────
$pwErrors = [];
if (strlen($password) < 8)               $pwErrors[] = 'at least 8 characters';
if (!preg_match('/[A-Z]/', $password))   $pwErrors[] = 'an uppercase letter';
if (!preg_match('/[a-z]/', $password))   $pwErrors[] = 'a lowercase letter';
if (!preg_match('/[0-9]/', $password))   $pwErrors[] = 'a number';
if (!preg_match('/[^A-Za-z0-9\s]/', $password)) $pwErrors[] = 'a special character';
if (preg_match('/\s/', $password))       $pwErrors[] = 'no spaces';

if (!empty($pwErrors)) {
    redirect('weak_password', $back);
}

// ── Password match ────────────────────────────────────────────
if ($password !== $confirm_password) {
    redirect('password_mismatch', $back);
}

// ── DB: duplicate email ───────────────────────────────────────
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) { $stmt->close(); redirect('email_exists', $back); }
$stmt->close();

// ── DB: duplicate username ────────────────────────────────────
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) { $stmt->close(); redirect('username_exists', $back); }
$stmt->close();

// ── Hash password (Argon2id — requires PHP 7.3+ with libargon2) ──
if (!defined('PASSWORD_ARGON2ID')) {
    redirect('Server does not support Argon2. Please contact the administrator.');
}
$hashed = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,   // 64 MB
    'time_cost'   => 4,       // 4 iterations
    'threads'     => 2,       // 2 parallel threads
]);

// ── Insert ────────────────────────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO users
        (first_name, last_name, date_of_birth, email, phone, service_address, username, password)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    redirect('Registration failed. Please try again.', $back);
}

$stmt->bind_param(
    "ssssssss",
    $first_name,
    $last_name,
    $date_of_birth,
    $email,
    $phone,
    $service_address,
    $username,
    $hashed
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: login.php?registered=1");
    exit();
} else {
    error_log("Registration insert error: " . $stmt->error);
    $stmt->close();
    redirect('Registration failed. Please try again.', $back);
}