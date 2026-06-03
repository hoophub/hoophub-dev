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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * { font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .navbar {
            backdrop-filter: blur(20px);
            background: rgba(0, 0, 0, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            color: #fff !important;
        }

        .navbar-brand i { color: #ffd700; }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 5rem 0 2rem;
        }

        .auth-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .auth-header {
            text-align: center;
            padding: 3.5rem 3rem 2rem;
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
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-8px); }
            60% { transform: translateY(-4px); }
        }

        .auth-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .auth-subtitle { color: #64748b; font-size: 1.1rem; }

        .auth-form { padding: 3rem; }

        .form-floating { margin-bottom: 1.5rem; position: relative; }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            height: 64px;
            font-size: 1rem;
            font-weight: 500;
            background: rgba(255,255,255,0.8);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.15);
            background: white;
            transform: translateY(-2px);
        }

        .form-floating > label {
            font-weight: 500;
            color: #475569;
            padding-left: 1rem;
        }

        /* Password toggle */
        .password-wrapper { position: relative; }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
            padding: 4px;
            transition: color 0.2s;
        }

        .password-toggle:hover { color: #667eea; }

        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            border-radius: 16px;
            padding: 1.25rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 12px 32px rgba(102,126,234,0.4);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(102,126,234,0.5);
            color: white;
        }

        .auth-footer {
            padding: 2rem 3rem 3rem;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            background: rgba(255,255,255,0.5);
        }

        .auth-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-link:hover { color: #5a67d8; text-decoration: underline; }

        .floating-ball {
            position: absolute;
            font-size: 3rem;
            opacity: 0.05;
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }

        .floating-ball:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
        .floating-ball:nth-child(2) { top: 60%; right: 10%; animation-delay: 2s; }
        .floating-ball:nth-child(3) { bottom: 20%; left: 10%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @media (max-width: 576px) {
            .auth-form { padding: 2rem 1.5rem; }
            .auth-header { padding: 2.5rem 1.5rem 1.5rem; }
            .auth-footer { padding: 1.5rem 1.5rem 2rem; }
        }
    </style>
</head>
<body>
    <div class="floating-ball"><i class="fas fa-basketball-ball text-primary"></i></div>
    <div class="floating-ball"><i class="fas fa-basketball-ball text-success"></i></div>
    <div class="floating-ball"><i class="fas fa-basketball-ball text-warning"></i></div>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
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

                        <form method="POST" action="login-process.php" class="auth-form" id="loginForm">

                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="email@example.com" required
                                    value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                                <label for="email"><i class="fas fa-envelope me-1"></i>Email Address</label>
                            </div>

                            <div class="form-floating password-wrapper">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-1"></i>Password</label>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>

                            <button type="submit" class="btn btn-submit w-100">
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
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() { this.closest('.form-floating').style.transform = 'scale(1.02)'; });
            input.addEventListener('blur',  function() { this.closest('.form-floating').style.transform = 'scale(1)'; });
        });
    </script>

    <script>
    <?php if (isset($_GET['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: "<?php echo addslashes(htmlspecialchars($_GET['error'])); ?>",
            confirmButtonColor: '#667eea'
        });
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'Your account is ready. Please sign in.',
            confirmButtonColor: '#667eea'
        });
    <?php endif; ?>
    </script>
</body>
</html>