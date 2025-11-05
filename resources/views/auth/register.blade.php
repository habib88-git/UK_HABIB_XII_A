<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aplikasi POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- reCAPTCHA JS --}}
    {!! NoCaptcha::renderJs() !!}

    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --light-color: #f8fafc;
            --dark-color: #0f172a;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float-random 25s infinite ease-in-out;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            bottom: -100px;
            right: -100px;
            animation-delay: 7s;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            top: 50%;
            left: 50%;
            animation-delay: 14s;
        }

        @keyframes float-random {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(80px, -60px) rotate(90deg); }
            50% { transform: translate(-60px, 80px) rotate(180deg); }
            75% { transform: translate(60px, 40px) rotate(270deg); }
        }

        /* Particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            animation: particle-float 18s infinite;
        }

        @keyframes particle-float {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
        }

        /* Register Container */
        .register-container {
            max-width: 600px;
            width: 100%;
            border-radius: 30px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            animation: slideIn 1s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes slideIn {
            from {
                transform: scale(0.8) translateY(100px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .register-card {
            padding: 50px 45px;
            background: white;
            position: relative;
        }

        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 35px;
            animation: float-icon 3s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        @keyframes float-icon {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
        }

        .register-header h3 {
            font-size: 30px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .register-header p {
            color: #64748b;
            font-size: 14px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 14px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 14px 45px 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .input-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-control:focus ~ .input-icon {
            color: var(--primary-color);
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
            z-index: 10;
            font-size: 16px;
        }

        .toggle-password:hover {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.15);
        }

        /* Button */
        .btn-register {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:active {
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 13px;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--danger-color);
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert-danger li {
            margin: 5px 0;
        }

        /* Recaptcha */
        .recaptcha-wrapper {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        /* Login Link */
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #f1f5f9;
        }

        .login-link p {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }

        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                border-radius: 25px;
                margin: 20px 0;
            }

            .register-card {
                padding: 40px 30px;
            }

            .register-header h3 {
                font-size: 26px;
            }

            .register-header-icon {
                width: 70px;
                height: 70px;
                font-size: 30px;
            }
        }

        /* Loading Animation */
        .btn-register.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-register.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Progress Bar */
        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .password-strength.show {
            opacity: 1;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #ef4444;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #f59e0b;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #10b981;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="particles" id="particles"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-header-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Daftar Akun Baru</h3>
                <p>Buat akun untuk mengakses sistem POS</p>
            </div>

            {{-- Pesan error --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.process') }}" id="registerForm">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control"
                            placeholder="Masukkan nama lengkap"
                            required
                            autofocus>
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="nama@email.com"
                            required>
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="no_telp" class="form-label">No. Telepon</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="no_telp"
                            id="no_telp"
                            class="form-control"
                            placeholder="08xxxxxxxxxx"
                            required>
                        <span class="input-icon"><i class="fas fa-phone"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat" class="form-label">Alamat</label>
                    <div class="input-wrapper">
                        <textarea
                            name="alamat"
                            id="alamat"
                            class="form-control"
                            placeholder="Masukkan alamat lengkap"></textarea>
                        <span class="input-icon" style="top: 20px;"><i class="fas fa-map-marker-alt"></i></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sandi" class="form-label">Sandi</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="sandi"
                            id="sandi"
                            class="form-control"
                            placeholder="Minimal 8 karakter"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sandi_confirmation" class="form-label">Konfirmasi Sandi</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="sandi_confirmation"
                            id="sandi_confirmation"
                            class="form-control"
                            placeholder="Ulangi sandi"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword2">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="recaptcha-wrapper">
                    {!! NoCaptcha::display() !!}
                </div>

                <button type="submit" class="btn-register" id="registerBtn">
                    <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                </button>
            </form>

            <div class="login-link">
                <p>Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script>
        // Toggle Password Functions
        function setupPasswordToggle(buttonId, inputId) {
            document.getElementById(buttonId).addEventListener('click', function() {
                const passwordInput = document.getElementById(inputId);
                const icon = this.querySelector('i');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        setupPasswordToggle('togglePassword1', 'sandi');
        setupPasswordToggle('togglePassword2', 'sandi_confirmation');

        // Password Strength Indicator
        const passwordInput = document.getElementById('sandi');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthContainer = document.getElementById('passwordStrength');

        passwordInput.addEventListener('input', function() {
            const password = this.value;

            if (password.length === 0) {
                strengthContainer.classList.remove('show');
                return;
            }

            strengthContainer.classList.add('show');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'password-strength-bar';

            if (strength <= 1) {
                strengthBar.classList.add('weak');
            } else if (strength <= 2) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });

        // Generate Particles
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 40; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 18 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particlesContainer.appendChild(particle);
        }

        // Form Submit Animation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('registerBtn');
            btn.classList.add('loading');
            btn.innerHTML = '';
        });

        // Input Focus Animation
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Phone Number Validation
        document.getElementById('no_telp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
