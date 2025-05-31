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

<body class="login-bg min-h-screen h-screen flex flex-col justify-center items-center">

    <!-- Main Card -->
    <!-- MAIN CARD -->
    <main class="main-frame-bg w-full max-w-[98vw] md:max-w-[90vw] lg:max-w-[80vw] xl:max-w-[70vw] 2xl:max-w-[1400px]
    bg-white rounded-2xl shadow-2xl flex flex-col lg:flex-row overflow-hidden my-2 md:my-8
    min-h-[600px] max-h-[1000px]">

        <!-- LEFT SIDE -->
        <section class="relative flex flex-col bg-white w-full lg:basis-[53.5%]">
            <div class="w-full h-auto lg:h-[600px] bg-white px-2 pt-4 md:px-8 md:pt-6">
                <div class="flex flex-row items-center">
                    <img
                        src="{{ asset('storage/logo/logo_kz.png') }}"
                        class="w-[36px] h-[36px] md:w-[80px] md:h-[80px] lg:w-[120px] lg:h-[120px] object-contain"
                        alt="Logo KZ"
                        width="120"
                        height="120" />
                    <span class="ml-2 md:ml-6 text-base md:text-2xl lg:text-3xl font-extrabold tracking-tight" style="font-family: Montserrat, sans-serif;">
                        Selamat datang di Toko KZ Family
                    </span>
                </div>
                <div class="flex flex-row items-end justify-start mt-2 md:mt-5 lg:mt-5">
                    <img src="{{ asset('storage/logo/login_cuy.png') }}"
                        class="w-full max-w-[320px] md:max-w-[500px] lg:max-w-[817px] h-auto object-contain"
                        alt="Login Cuy" />
                </div>
            </div>
        </section>

        <!-- RIGHT SIDE -->
        <section class="flex flex-col justify-center items-center w-full lg:basis-[46.5%] bg-[#7DBFD9]">
            <div class="bg-white p-3 md:p-6 lg:p-10 rounded-2xl w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-[416px] mx-auto shadow-lg flex flex-col justify-center my-4 md:my-8 lg:my-0">
                <p class="text-lg md:text-xl font-bold text-gray-700 mb-3 text-center">Sign In</p>
                <p class="text-sm md:text-base text-gray-700 mb-3 text-center">to Website Toko Online KZ Family</p>
                <form action="{{ route('login') }}" method="POST" class="space-y-4 w-full text-gray-700 text-sm md:text-base">
                    @csrf
                    <div>
                        <label for="email" class="block mb-1 font-semibold text-sm">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan Email Anda"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-500 @enderror" />
                        @error('email')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="relative">
                        <label for="password" class="block mb-1">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter password"
                            class="w-full border rounded-md px-3 py-2 pr-10 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600
                            @error('password') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                    <button type="submit"
                        class="bg-[#57aed1] text-white text-sm md:text-base font-bold px-4 py-2 w-full rounded-lg hover:bg-[#001766] transition">
                        Login
                    </button>
                </form>
                <p class="mt-3 text-xs md:text-sm text-gray-800 text-center">
                    <strong>Belum Punya Akun?</strong>
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar Sekarang</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>