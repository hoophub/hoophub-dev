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
    <title>Register - HoopHub 🏀</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            color: #fff !important;
        }

        .navbar-brand i { color: #ffd700; }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 0 3rem;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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
            padding: 3rem 3rem 2rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-bottom: 1px solid rgba(102, 126, 234, 0.2);
        }

        .auth-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            font-size: 2rem;
            color: white;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-8px); }
            60% { transform: translateY(-4px); }
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.4rem;
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            color: #64748b;
            font-size: 1rem;
            font-weight: 400;
        }

        /* Section Labels */
        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #667eea;
            margin: 1.5rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-label:first-child { margin-top: 0; }

        .auth-form { padding: 2.5rem 3rem; }

        .row.g-3 { margin-bottom: 0; }

        .form-floating {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.2rem 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 60px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            background: white;
            transform: translateY(-1px);
        }

        .form-control.is-valid { border-color: #22c55e; }
        .form-control.is-invalid { border-color: #ef4444; }

        .form-floating > label {
            font-weight: 500;
            color: #475569;
            font-size: 0.9rem;
            padding-left: 1rem;
        }

        /* Char counter */
        .char-counter {
            position: absolute;
            right: 12px;
            bottom: -20px;
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .char-counter.warn { color: #f59e0b; }
        .char-counter.danger { color: #ef4444; }

        /* Password Strength */
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

        .strength-bar-container {
            margin-top: 0.5rem;
            padding: 0 2px;
        }

        .strength-track {
            height: 5px;
            background: #e2e8f0;
            border-radius: 99px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.4s ease, background 0.4s ease;
            width: 0%;
        }

        .strength-label {
            font-size: 0.72rem;
            font-weight: 600;
            margin-top: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .strength-text { transition: color 0.3s; }

        /* Requirements checklist */
        .req-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 12px;
            margin-top: 8px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .req-item {
            font-size: 0.72rem;
            font-weight: 500;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.25s;
        }

        .req-item.met { color: #22c55e; }
        .req-item i { font-size: 0.65rem; }

        /* Submit Button */
        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            border-radius: 14px;
            padding: 1.1rem;
            font-size: 1.05rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.5);
            color: white;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .auth-footer {
            padding: 1.75rem 3rem 2.5rem;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            background: rgba(255, 255, 255, 0.5);
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
            .req-list { grid-template-columns: 1fr; }
        }

        /* Input with icon inside */
        .input-icon-group { position: relative; }
        .input-icon-group .form-control { padding-right: 2.75rem; }
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
                <a class="nav-link" href="login.php">🔐 Login</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-7 col-md-9">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h1 class="auth-title">Create Account</h1>
                            <p class="auth-subtitle">Join 50K+ players booking courts worldwide</p>
                        </div>

                        <form method="POST" action="register-process.php" class="auth-form" id="registerForm" novalidate>

                            <!-- Personal Info -->
                            <div class="section-label">
                                <i class="fas fa-id-card"></i> Personal Information
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="form-floating" style="position:relative;">
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            placeholder="First Name" required maxlength="50"
                                            value="<?php echo isset($_GET['first_name']) ? htmlspecialchars($_GET['first_name']) : ''; ?>">
                                        <label for="first_name"><i class="fas fa-user me-1"></i>First Name</label>
                                        <span class="char-counter" id="fn-count">0/50</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating" style="position:relative;">
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            placeholder="Last Name" required maxlength="50"
                                            value="<?php echo isset($_GET['last_name']) ? htmlspecialchars($_GET['last_name']) : ''; ?>">
                                        <label for="last_name"><i class="fas fa-user me-1"></i>Last Name</label>
                                        <span class="char-counter" id="ln-count">0/50</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating mt-1">
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                    placeholder="Date of Birth" required
                                    max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>"
                                    value="<?php echo isset($_GET['dob']) ? htmlspecialchars($_GET['dob']) : ''; ?>">
                                <label for="date_of_birth"><i class="fas fa-calendar-alt me-1"></i>Date of Birth</label>
                            </div>

                            <!-- Contact Info -->
                            <div class="section-label">
                                <i class="fas fa-address-book"></i> Contact Information
                            </div>

                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="email@example.com" required
                                    value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                                <label for="email"><i class="fas fa-envelope me-1"></i>Email Address</label>
                            </div>

                            <div class="form-floating">
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    placeholder="Phone Number" required maxlength="20"
                                    pattern="[0-9+\-\s\(\)]{7,20}"
                                    value="<?php echo isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : ''; ?>">
                                <label for="phone"><i class="fas fa-phone me-1"></i>Phone Number</label>
                            </div>

                            <div class="form-floating">
                                <input type="text" class="form-control" id="service_address" name="service_address"
                                    placeholder="Service Address" required maxlength="255"
                                    value="<?php echo isset($_GET['address']) ? htmlspecialchars($_GET['address']) : ''; ?>">
                                <label for="service_address"><i class="fas fa-map-marker-alt me-1"></i>Service Address</label>
                            </div>

                            <!-- Account Info -->
                            <div class="section-label">
                                <i class="fas fa-shield-alt"></i> Account & Security
                            </div>

                            <div class="form-floating">
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="username" required maxlength="20"
                                    value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
                                <label for="username"><i class="fas fa-at me-1"></i>Username</label>
                            </div>

                            <!-- Password -->
                            <div class="form-floating password-wrapper input-icon-group">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Password" required minlength="8">
                                <label for="password"><i class="fas fa-lock me-1"></i>Password</label>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <!-- Strength Bar -->
                            <div class="strength-bar-container">
                                <div class="strength-track">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-label">
                                    <span class="strength-text text-muted" id="strengthText">Enter a password</span>
                                    <span class="text-muted" id="strengthScore" style="font-size:0.7rem;"></span>
                                </div>
                                <div class="req-list" id="reqList">
                                    <div class="req-item" id="req-length"><i class="fas fa-circle"></i> 8+ characters</div>
                                    <div class="req-item" id="req-upper"><i class="fas fa-circle"></i> Uppercase letter</div>
                                    <div class="req-item" id="req-lower"><i class="fas fa-circle"></i> Lowercase letter</div>
                                    <div class="req-item" id="req-number"><i class="fas fa-circle"></i> Number</div>
                                    <div class="req-item" id="req-special"><i class="fas fa-circle"></i> Special character</div>
                                    <div class="req-item" id="req-nospace"><i class="fas fa-circle"></i> No spaces</div>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-floating password-wrapper input-icon-group mt-3">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    placeholder="Confirm Password" required>
                                <label for="confirm_password"><i class="fas fa-lock-open me-1"></i>Confirm Password</label>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="match-msg" style="font-size:0.75rem; font-weight:600; margin-top:4px; display:none;"></div>

                            <button type="submit" class="btn btn-submit w-100" id="submitBtn">
                                <i class="fas fa-rocket me-2"></i>Create My Account
                            </button>
                        </form>

                        <div class="auth-footer">
                            <p class="mb-0 text-muted">
                                Already have an account?
                                <a href="login.php" class="auth-link">
                                    <i class="fas fa-arrow-right me-1"></i>Sign in now
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
        /* ── Password toggle ── */
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        /* ── Character counters ── */
        function initCharCounter(inputId, counterId, max) {
            const input   = document.getElementById(inputId);
            const counter = document.getElementById(counterId);
            input.addEventListener('input', () => {
                const len = input.value.length;
                counter.textContent = `${len}/${max}`;
                counter.className = 'char-counter' +
                    (len >= max ? ' danger' : len >= max * 0.8 ? ' warn' : '');
            });
        }
        initCharCounter('first_name', 'fn-count', 50);
        initCharCounter('last_name',  'ln-count', 50);

        /* ── Password strength ── */
        const checks = {
            length:  v => v.length >= 8,
            upper:   v => /[A-Z]/.test(v),
            lower:   v => /[a-z]/.test(v),
            number:  v => /[0-9]/.test(v),
            special: v => /[^A-Za-z0-9\s]/.test(v),
            nospace: v => !/\s/.test(v) && v.length > 0,
        };

        const levels = [
            { min: 0, label: 'Too weak',    color: '#ef4444', pct: 15  },
            { min: 2, label: 'Weak',         color: '#f97316', pct: 33  },
            { min: 3, label: 'Fair',         color: '#eab308', pct: 55  },
            { min: 4, label: 'Good',         color: '#3b82f6', pct: 75  },
            { min: 5, label: 'Strong',       color: '#22c55e', pct: 90  },
            { min: 6, label: '💪 Very strong', color: '#16a34a', pct: 100 },
        ];

        const pwInput   = document.getElementById('password');
        const fill      = document.getElementById('strengthFill');
        const labelEl   = document.getElementById('strengthText');
        const scoreEl   = document.getElementById('strengthScore');
        const submitBtn = document.getElementById('submitBtn');

        function updateStrength() {
            const val  = pwInput.value;
            const keys = Object.keys(checks);
            let score  = 0;

            keys.forEach(k => {
                const met = checks[k](val);
                if (met) score++;
                const el = document.getElementById(`req-${k}`);
                if (el) {
                    el.classList.toggle('met', met);
                    el.querySelector('i').className = met ? 'fas fa-check-circle' : 'fas fa-circle';
                }
            });

            if (val.length === 0) {
                fill.style.width = '0%';
                labelEl.textContent = 'Enter a password';
                labelEl.style.color = '#94a3b8';
                scoreEl.textContent = '';
                return;
            }

            const lvl = [...levels].reverse().find(l => score >= l.min) || levels[0];
            fill.style.width      = lvl.pct + '%';
            fill.style.background = lvl.color;
            labelEl.textContent   = lvl.label;
            labelEl.style.color   = lvl.color;
            scoreEl.textContent   = `${score}/6 rules`;

            validateMatch();
        }

        /* ── Match check ── */
        const confirmInput = document.getElementById('confirm_password');
        const matchMsg     = document.getElementById('match-msg');

        function validateMatch() {
            const pw  = pwInput.value;
            const cpw = confirmInput.value;
            if (cpw.length === 0) { matchMsg.style.display = 'none'; return; }
            if (pw === cpw) {
                matchMsg.style.display = 'block';
                matchMsg.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i><span style="color:#22c55e">Passwords match</span>';
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
            } else {
                matchMsg.style.display = 'block';
                matchMsg.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i><span style="color:#ef4444">Passwords do not match</span>';
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid');
            }
        }

        pwInput.addEventListener('input', updateStrength);
        confirmInput.addEventListener('input', validateMatch);

        /* ── Phone – digits/symbols only ── */
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\-\s\(\)]/g, '');
        });

        /* ── Name – letters/spaces only ── */
        ['first_name', 'last_name'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                this.value = this.value.replace(/[^A-Za-z\s\-'\.]/g, '');
            });
        });

        /* ── Form submit validation ── */
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const pw  = pwInput.value;
            const cpw = confirmInput.value;

            // All required checks met?
            const allMet = Object.values(checks).every(fn => fn(pw));
            if (!allMet) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Weak Password',
                    text: 'Your password must meet all 6 requirements before continuing.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            if (pw !== cpw) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Your passwords do not match. Please try again.',
                    confirmButtonColor: '#667eea'
                });
                confirmInput.focus();
                return;
            }
        });

        /* ── Input focus animations ── */
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus',  function() { this.closest('.form-floating').style.transform = 'scale(1.01)'; });
            input.addEventListener('blur',   function() { this.closest('.form-floating').style.transform = 'scale(1)'; });
        });
    </script>

    <!-- SweetAlert for PHP error/success messages -->
    <script>
    <?php if (isset($_GET['error'])): ?>
        const errorType = "<?php echo addslashes(htmlspecialchars($_GET['error'])); ?>";
        const messages = {
            email_exists:       ['Email Already Exists',   'Please use a different email address.'],
            username_exists:    ['Username Taken',         'That username is already in use. Please choose another.'],
            password_mismatch:  ['Password Mismatch',      'Your passwords do not match. Please try again.'],
            weak_password:      ['Weak Password',          'Your password does not meet the required strength.'],
            invalid_dob:        ['Invalid Date of Birth',  'Please enter a valid date of birth.'],
        };
        const [title, text] = messages[errorType] || ['Error', errorType];
        Swal.fire({ icon: 'error', title, text, confirmButtonColor: '#667eea' });

    <?php elseif (isset($_GET['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Account Created!',
            text: 'You are now registered successfully.',
            confirmButtonText: 'Go to Login',
            confirmButtonColor: '#667eea'
        }).then(r => { if (r.isConfirmed) window.location.href = 'login.php'; });
    <?php endif; ?>
    </script>
</body>
</html>