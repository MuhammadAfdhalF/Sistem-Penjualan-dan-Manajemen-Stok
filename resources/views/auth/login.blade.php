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
    </style>
</head>

<body class="bg-[#a9bec9] min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Main Card -->
    <main class="max-w-7xl w-full max-h-[600px] bg-white rounded-sm shadow-lg flex flex-col md:flex-row overflow-hidden">

        <!-- Left side -->
        <section class="md:w-1/2 p-8 flex flex-col">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white/90">____ \<br>------------</h1>
            </div>
            <div class="flex-grow flex items-center justify-center min-h-[300px]">
                <img
                    src="{{ asset('storage/logo/logo_kz.png') }}"
                    alt="Illustration"
                    class="max-w-full max-h-[350px] object-contain" />
            </div>
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white/90">____ \<br>------------</h1>
            </div>
        </section>

        <!-- Right section -->
        <section class="md:w-1/2 p-8 flex flex-col justify-center items-center">
            <div class="bg-white p-6 rounded-2xl w-full max-w-md" style="box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
                <p class="text-xl font-bold text-gray-700 mb-6 text-center">Sign In</p>
                <p class="text-sm text-gray-700 mb-3 text-center">to Website Toko Online KZ Family</p>

                <div class="flex items-center text-gray-500 text-xs mb-6">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-4 text-gray-700 text-sm">
                    @csrf
                    <div>
                        <label for="email" class="block mb-1"> Email </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan Email Anda"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600
                            @error('email') border-red-500 @enderror" />
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
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit"
                        class="bg-[#57aed1] text-white text-xs font-semibold px-6 py-2 w-full max-w-xs mx-auto rounded-md hover:bg-[#001766] transition block">
                        Login
                    </button>
                </form>

                <p class="mt-4 text-xs text-gray-800 text-center">
                    <strong>Belum Punya Akun??</strong>
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar Sekarang</a>
                </p>

            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer
        class="w-full max-w-7xl bg-[#57aed1] text-white text-xs py-3 px-8 flex justify-between items-center mt-4 rounded-sm shadow-lg">
        <p>Selamat datang di Toko KZ Family, tempat belanja terpercaya Anda.</p>
    </footer>

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