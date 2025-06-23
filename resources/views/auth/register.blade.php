<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register Page</title>
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
                <img src="{{ asset('storage/logo/logo_kz.png') }}" alt="Logo KZ" class="w-20 lg:w-28 h-auto">
                <h1 class="ml-4 text-[1.165rem] lg:text-[1.575rem] font-extrabold text-black text-left" style="font-family: 'Liita', sans-serif;">
                    Daftar Akun Baru di Toko KZ Family
                </h1>
            </div>

            <!-- Gambar -->
            <div class="w-full flex justify-center lg:justify-start mt-2 lg:mt-[-20px]">
                <img src="{{ asset('storage/logo/login_cuy.png') }}" alt="Register Cuy"
                    class="w-[360px] lg:w-[700px] h-[240px] lg:h-[380px] object-contain">
            </div>
        </section>

        <!-- RIGHT PANEL -->
        <section class="w-full lg:w-[46.85%] bg-[#7DBFD9]/90 flex items-center justify-center p-6 lg:p-12">
            <div class="bg-white p-4 sm:p-6 lg:p-10 rounded-2xl w-full max-w-full sm:max-w-lg md:max-w-xl lg:max-w-[600px] mx-auto shadow-lg">
                <h2 class="text-lg md:text-xl font-bold text-gray-700 mb-3 text-center">Daftar Akun Baru</h2>
                <p class="text-sm md:text-base text-gray-700 mb-3 text-center">Isi data untuk membuat akun di Toko KZ Family</p>

                <form action="{{ route('register') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full text-gray-700 text-sm md:text-base">
                    @csrf

                    <div>
                        <label for="nama" class="block mb-1 font-semibold text-sm">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('nama') border-red-500 @enderror" />
                        @error('nama')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block mb-1 font-semibold text-sm">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan Email Anda"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('email') border-red-500 @enderror" />
                        @error('email')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="no_hp" class="block mb-1 font-semibold text-sm">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="Masukkan nomor HP"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('no_hp') border-red-500 @enderror" />
                        @error('no_hp')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_lahir" class="block mb-1 font-semibold text-sm">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('tanggal_lahir') border-red-500 @enderror" />
                        @error('tanggal_lahir')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_pelanggan" class="block mb-1 font-semibold text-sm">Jenis Pelanggan</label>
                        <select id="jenis_pelanggan" name="jenis_pelanggan"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-blue-600 @error('jenis_pelanggan') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis Pelanggan --</option>
                            <option value="Toko Kecil" {{ old('jenis_pelanggan') == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                            <option value="Individu" {{ old('jenis_pelanggan') == 'Individu' ? 'selected' : '' }}>Individu</option>
                        </select>
                        @error('jenis_pelanggan')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block mb-1 font-semibold text-sm">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap"
                            class="w-full border rounded-lg px-3 py-2 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <input type="hidden" name="role" value="pelanggan" />

                    <div class="relative">
                        <label for="password" class="block mb-1 font-semibold text-sm">Password</label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password"
                            class="w-full border rounded-lg px-3 py-2 pr-10 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="relative">
                        <label for="password_confirmation" class="block mb-1 font-semibold text-sm">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Masukkan konfirmasi password"
                            class="w-full border rounded-lg px-3 py-2 pr-10 text-sm md:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('password_confirmation') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password_confirmation')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Button & Link -->
                    <div class="col-span-1 md:col-span-2 flex flex-col gap-2 mt-2">
                        <button type="submit"
                            class="bg-[#57aed1] text-white text-sm md:text-base font-bold px-4 py-2 w-full rounded-lg hover:bg-[#001766] transition">
                            Register
                        </button>
                        <p class="mt-2 text-xs md:text-sm text-gray-800 text-center">
                            <strong>Sudah punya akun?</strong>
                            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Masuk di sini</a>
                        </p>
                    </div>
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