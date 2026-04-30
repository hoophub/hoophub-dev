<?php 
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HoopHub 🏀</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            backdrop-filter: blur(20px);
            background: rgba(0, 0, 0, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .navbar-brand { font-weight: 700; font-size: 1.6rem; color: #fff !important; }
        .navbar-brand i { color: #ffd700; }
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        .auth-card {
            background: rgba(255,255,255,0.95);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: slideUp 0.8s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .auth-card::before {
            content: '';
            display: block;
            height: 4px;
            background: var(--primary-gradient);
        }
        .auth-header {
            text-align: center;
            padding: 3rem 3rem 2rem;
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border-bottom: 1px solid rgba(102,126,234,0.2);
        }
        .auth-icon {
            width: 90px; height: 90px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.2rem; color: white;
            box-shadow: 0 20px 40px rgba(102,126,234,0.3);
        }
        .auth-title { font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
        .auth-subtitle { color: #64748b; font-size: 1.1rem; }
        .auth-form { padding: 3rem; }
        .form-floating { margin-bottom: 1.5rem; }
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            height: 64px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.15);
            transform: translateY(-2px);
        }
        .form-floating > label { font-weight: 500; color: #475569; padding-left: 1rem; }
        .btn-submit {
            background: var(--primary-gradient);
            border: none; border-radius: 16px;
            padding: 1.25rem; font-size: 1.1rem; font-weight: 600;
            color: white; text-transform: uppercase; letter-spacing: 0.5px;
            box-shadow: 0 12px 32px rgba(102,126,234,0.4);
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(102,126,234,0.5);
            color: white;
        }
        .alert-danger {
            border: none; border-radius: 16px; font-weight: 500;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
        .auth-footer {
            padding: 2rem 3rem 3rem;
            text-align: center;
            border-top: 1px solid #f1f5f9;
        }
        .auth-link { color: #667eea; font-weight: 600; text-decoration: none; }
        .auth-link:hover { color: #5a67d8; text-decoration: underline; }
        @media (max-width: 576px) {
            .auth-form, .auth-header, .auth-footer { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-basketball-ball me-2"></i>HoopHub
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link me-3" href="index.html">🏠 Home</a>
                <a class="nav-link" href="register.php">📝 Register</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">

            <?php if (isset($_GET['error'])): ?>
                <div class="row justify-content-center mb-4">
                    <div class="col-lg-6 col-md-8">
                        <div class="alert alert-danger shadow-lg">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-6 col-md-8">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <h1 class="auth-title">Welcome Back</h1>
                            <p class="auth-subtitle">Sign in to book your favorite courts</p>
                        </div>

                        <form method="POST" action="login-process.php" class="auth-form">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                                <label for="email"><i class="fas fa-envelope me-1"></i>Email Address</label>
                            </div>

                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-1"></i>Password</label>
                            </div>

                            <button type="submit" class="btn btn-submit w-100 mt-2">
                                <i class="fas fa-arrow-right me-2"></i>Sign In to HoopHub
                            </button>
                        </form>

                        <div class="auth-footer">
                            <p class="mb-0 text-muted">
                                Don't have an account?
                                <a href="register.php" class="auth-link">
                                    <i class="fas fa-user-plus me-1"></i>Create one now
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>