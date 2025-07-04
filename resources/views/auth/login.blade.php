<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Liita&display=swap');

        body {
            font-family: 'Open Sans', sans-serif;
        }

        .login-bg {
            background-image: url('/storage/logo/login_dasar.png');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="login-bg min-h-screen flex items-center justify-center">

    <main class="flex flex-col lg:flex-row w-full max-w-[1380px] lg:h-[640px] h-auto mx-4 lg:mx-auto bg-white rounded-2xl overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.5)] backdrop-blur-sm">

        <!-- LEFT PANEL -->
        <section class="w-full lg:w-[53.15%] bg-white flex flex-col justify-start items-start px-6 py-6 lg:px-12 lg:py-10">

            <!-- Logo + Headline -->
            <div class="flex flex-row items-center w-full mb-4 lg:mb-8">
                <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo KZ" class="w-20 lg:w-28 h-auto">
                <h1 class="ml-4 text-[1.175rem] lg:text-[1.675rem] font-extrabold text-black text-left" style="font-family: 'Liita', sans-serif;">
                    Selamat datang di Toko KZ Family
                </h1>
            </div>

            <!-- Gambar utama -->
            <div class="w-full flex justify-center lg:justify-start mt-2 lg:mt-[-20px]">
                <img src="{{ asset('storage/logo/login_cuy.png') }}" alt="Login Cuy"
                    class="w-[360px] lg:w-[700px] h-[240px] lg:h-[380px] object-contain">
            </div>
        </section>

        <!-- RIGHT PANEL -->
        <section class="w-full lg:w-[46.85%] bg-[#7DBFD9]/90 flex items-center justify-center p-6 lg:p-12">
            <div class="bg-white p-6 lg:p-8 rounded-2xl w-full max-w-md">
                <p class="text-xl font-bold text-center text-gray-700 mb-2">Sign In</p>
                <p class="text-sm text-center text-gray-600 mb-6">to Website Toko Online KZ Family</p>

                <form action="{{ route('login') }}" method="POST" class="space-y-4 text-sm text-gray-700">
                    @csrf
                    <div>
                        <label for="email" class="block mb-1 font-semibold">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="Masukkan Email Anda"
                            class="w-full border rounded-lg px-3 py-2 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-500 @enderror" />
                        @error('email')
                        <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="relative">
                        <label for="password" class="block mb-1 font-semibold">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password"
                            class="w-full border rounded-lg px-3 py-2 pr-10 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-3 top-9 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                        <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-[#57aed1] hover:bg-[#001766] text-white font-semibold py-2 rounded-lg transition duration-200">
                        Login
                    </button>

                    <p class="text-center text-sm mt-3">
                        <strong>Belum Punya Akun?</strong>
                        <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">Daftar Sekarang</a>
                    </p>
                </form>
            </div>
        </section>
    </main>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>

</html>