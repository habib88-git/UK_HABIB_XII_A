<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
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
            opacity: 0.5;
            animation: float-random 20s infinite ease-in-out;
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
            animation-delay: 5s;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            top: 50%;
            left: 50%;
            animation-delay: 10s;
        }

        @keyframes float-random {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(100px, -50px) rotate(90deg); }
            50% { transform: translate(-50px, 100px) rotate(180deg); }
            75% { transform: translate(50px, 50px) rotate(270deg); }
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
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            animation: particle-float 15s infinite;
        }

        @keyframes particle-float {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(100px); opacity: 0; }
        }

        /* Login Container */
        .login-container {
            display: flex;
            max-width: 1000px;
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

        /* Left Side - Illustration */
        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
        }

        .illustration-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .illustration-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            animation: float-icon 3s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        @keyframes float-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .illustration-content h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            animation: fadeInUp 1s ease 0.3s backwards;
        }

        .illustration-content p {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 300;
            line-height: 1.6;
            animation: fadeInUp 1s ease 0.5s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Decorative Circles */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: pulse 4s ease-in-out infinite;
        }

        .deco-circle-1 {
            width: 200px;
            height: 200px;
            top: -50px;
            right: -50px;
            animation-delay: 0s;
        }

        .deco-circle-2 {
            width: 150px;
            height: 150px;
            bottom: -30px;
            left: -30px;
            animation-delay: 2s;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        /* Right Side - Form */
        .login-card {
            flex: 1;
            padding: 60px 50px;
            background: white;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            animation: rotate-scale 3s ease-in-out infinite;
        }

        @keyframes rotate-scale {
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(5deg) scale(1.05); }
        }

        .login-header h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        .login-header p {
            color: #64748b;
            font-size: 14px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 14px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 15px;
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

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: var(--primary-color);
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 15px;
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

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 14px;
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

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: var(--success-color);
        }

        /* Recaptcha */
        .recaptcha-wrapper {
            display: flex;
            justify-content: center;
            margin: 25px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                border-radius: 25px;
            }

            .login-illustration {
                padding: 40px 30px;
            }

            .illustration-icon {
                width: 90px;
                height: 90px;
                font-size: 40px;
            }

            .illustration-content h2 {
                font-size: 24px;
            }

            .login-card {
                padding: 40px 30px;
            }

            .login-header h3 {
                font-size: 24px;
            }
        }

        /* Loading Animation */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
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

    <div class="login-container">
        <!-- Left Side - Illustration -->
        <div class="login-illustration">
            <div class="deco-circle deco-circle-1"></div>
            <div class="deco-circle deco-circle-2"></div>

            <div class="illustration-content">
                <div class="illustration-icon">
                    🔐
                </div>
                <h2>Selamat Datang di Sistem POS!</h2>
                <p>Login untuk mengakses sistem kasir dan kelola transaksi penjualan Anda</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-icon">
                    👤
                </div>
                <h3>Login Sistem POS</h3>
                <p>Masukkan kredensial untuk akses kasir atau admin</p>
            </div>

            {{-- Pesan error --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Pesan sukses --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="nama@gmail.com"
                            required
                            autofocus>
                        <span class="input-icon">✉️</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sandi" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="sandi"
                            id="sandi"
                            class="form-control"
                            placeholder="Masukkan password"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="recaptcha-wrapper">
                    {!! NoCaptcha::display() !!}
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    Masuk Sekarang
                </button>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <script>
        // Toggle Password
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('sandi');
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

        // Generate Particles
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particlesContainer.appendChild(particle);
        }

        // Form Submit Animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
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
    </script>
</body>
</html>
