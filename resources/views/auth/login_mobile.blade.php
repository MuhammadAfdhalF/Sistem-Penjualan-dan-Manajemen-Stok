<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login KZ Family</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            border-top-left-radius: 2rem;
            border-top-right-radius: 2rem;
            background-color: #A3D9F5;
            padding: 2rem;
        }

        .form-control {
            border-radius: 1rem;
        }

        .btn-login {
            border-radius: 2rem;
            background-color: #007EA7;
            color: white;
        }

        .btn-login:hover {
            background-color: #005f7a;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<!-- <p class="text-center mt-3 small text-muted">
    <a href="{{ url('/login?force=desktop') }}" class="text-primary fw-semibold">
        Lihat versi desktop
    </a>
</p> -->

<body>
    <div class="container d-flex flex-column align-items-center justify-content-center min-vh-100 px-3">
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo/logo_kz.png') }}" alt="Logo KZ" width="80">
            <h4 class="mt-3 fw-bold">Toko KZ Family</h4>
            <p class="text-muted small">Masuk ke Aplikasi untuk berbelanja</p>
        </div>

        <div class="login-card shadow w-100" style="max-width: 400px;">
            <div class="text-center mb-3">
                <h5 class="fw-semibold">Login</h5>
            </div>

            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" placeholder="Email" value="{{ old('email') }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 position-relative">
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" placeholder="Password" id="passwordInput" required>
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-login w-100 py-2">Masuk</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-primary fw-semibold">Daftar di sini</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>