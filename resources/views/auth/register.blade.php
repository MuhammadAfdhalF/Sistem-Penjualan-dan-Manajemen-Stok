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

        body {
            font-family: 'Open Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-[#a9bec9] min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Main Card -->
    <main class="max-w-7xl w-full max-h-[900px] bg-white rounded shadow-lg flex flex-col md:flex-row overflow-auto">

        <!-- Left side -->
        <section class="md:w-1/2 p-8 flex flex-col justify-between">
            <div class="flex-grow flex items-center justify-center min-h-[300px]">
                <img src="{{ asset('storage/logo/logo_kz.png') }}" alt="Logo Toko KZ Family" class="max-w-full max-h-[350px] object-contain" />
            </div>

        </section>

        <!-- Right section -->
        <section class="md:w-1/2 p-8 flex flex-col justify-center items-center bg-white">
            <div class="w-full max-w-md p-6 rounded-2xl shadow-lg">
                <h2 class="text-xl font-bold text-gray-700 mb-4 text-center">Daftar Akun Baru</h2>
                <p class="text-sm text-gray-600 mb-6 text-center">Isi data untuk membuat akun di Toko KZ Family</p>

                <form action="{{ route('register') }}" method="POST" class="grid grid-cols-2 gap-6 text-gray-700 text-sm">
                    @csrf

                    <div>
                        <label for="nama" class="block mb-1 font-semibold text-gray-600">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('nama') border-red-500 @enderror" />
                        @error('nama')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block mb-1 font-semibold text-gray-600">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('email') border-red-500 @enderror" />
                        @error('email')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="no_hp" class="block mb-1 font-semibold text-gray-600">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="Masukkan nomor HP"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('no_hp') border-red-500 @enderror" />
                        @error('no_hp')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="umur" class="block mb-1 font-semibold text-gray-600">Umur</label>
                        <input type="number" id="umur" name="umur" value="{{ old('umur') }}" placeholder="Masukkan umur" min="1" max="150"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('umur') border-red-500 @enderror" />
                        @error('umur')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_pelanggan" class="block mb-1 font-semibold text-gray-600">Jenis Pelanggan</label>
                        <select id="jenis_pelanggan" name="jenis_pelanggan"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('jenis_pelanggan') border-red-500 @enderror">
                            <option value="">-- Pilih Jenis Pelanggan --</option>
                            <option value="Toko Kecil" {{ old('jenis_pelanggan') == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                            <option value="Individu" {{ old('jenis_pelanggan') == 'Individu' ? 'selected' : '' }}>Individu</option>
                        </select>
                        @error('jenis_pelanggan')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <label for="alamat" class="block mb-1 font-semibold text-gray-600">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap"
                            class="w-full border rounded-md px-3 py-2 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Hidden role -->
                    <input type="hidden" name="role" value="pelanggan" />

                    <div class="relative">
                        <label for="password" class="block mb-1 font-semibold text-gray-600">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            class="w-full border rounded-md px-3 py-2 pr-10 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('password') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>

                    </div>


                    <div class="relative">
                        <label for="password_confirmation" class="block mb-1 font-semibold text-gray-600">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Masukkan konfirmasi password"
                            class="w-full border rounded-md px-3 py-2 pr-10 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('password_confirmation') border-red-500 @enderror" />
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-2 text-gray-400 focus:outline-none"
                            style="top: 50%; transform: translateY(10%);">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password_confirmation')
                        <small class="text-red-500 text-xs mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <button type="submit"
                            class="bg-[#57aed1] text-white text-xs font-semibold px-6 py-2 w-full rounded-md hover:bg-[#001766] transition">
                            Register
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-xs text-gray-800 text-center">
                    <strong>Sudah punya akun?</strong>
                    <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Masuk di sini</a>
                </p>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer
        class="w-full max-w-7xl bg-[#57aed1] text-white text-xs py-3 px-8 flex justify-between items-center mt-4 rounded shadow-lg">
        <p>Selamat datang di Toko KZ Family, tempat belanja terpercaya Anda.</p>
    </footer>

    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>