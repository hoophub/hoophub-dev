<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=hoophub;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=" . urlencode("Fill all fields!"));
        exit();
    }

    // Fetch only required fields (no role column needed)
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=" . urlencode("Invalid credentials"));
        exit();
    }
}
?>