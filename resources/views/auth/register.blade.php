<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aplikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .register-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
        .register-card h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #28a745;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-success {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h3><i class="fas fa-user-plus"></i> Register</h3>

        {{-- Pesan error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.process') }}">
            @csrf
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>

            <div class="mb-3">
                <label>No. Telepon</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukkan no telepon" required>
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" placeholder="Masukkan alamat"></textarea>
            </div>

            <div class="mb-3">
                <label>Sandi</label>
                <input type="password" name="sandi" class="form-control" placeholder="Masukkan sandi" required>
            </div>

            <div class="mb-3">
                <label>Konfirmasi Sandi</label>
                <input type="password" name="sandi_confirmation" class="form-control" placeholder="Ulangi sandi" required>
            </div>

            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>
    </div>

    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
</body>
</html>
