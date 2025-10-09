<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .login-card {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
        .login-card h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #007bff;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-primary {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3><i class="fas fa-user-circle"></i> Login</h3>

        {{-- Pesan error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    placeholder="Masukkan email"
                    required
                    autofocus>
            </div>

            <div class="mb-3">
                <label for="sandi" class="form-label">Sandi</label>
                <input
                    type="password"
                    name="sandi"
                    id="sandi"
                    class="form-control"
                    placeholder="Masukkan sandi"
                    required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
</body>
</html>
