<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Menampilkan form untuk mengganti password.
     *
     * @return \Illuminate\View\View
     */
    public function changePasswordForm()
    {
        return view('auth.passwords.ganti_password', [
            'activeMenu' => 'ganti-password', // Sesuaikan dengan menu aktif di sidebar jika ada
            'breadcrumb' => [
                'Dashboard' => route('dashboard.index'),
                'Ganti Password' => '#',
            ]
        ]);
    }

    /**
     * Memproses permintaan penggantian password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('Password lama tidak sesuai.');
                }
            }],
            // Mengubah aturan password: hanya required, string, min:8, dan confirmed
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'current_password.string' => 'Password lama harus berupa teks.',
            'password.required' => 'Password baru wajib diisi.',
            'password.string' => 'Password baru harus berupa teks.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            // Pesan validasi kustom untuk aturan regex (dihapus karena aturan regex dihapus)
            // 'password.regex' => 'Password baru harus mengandung huruf besar, huruf kecil, angka, dan simbol.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        Log::info('Pengguna ID ' . $user->id . ' berhasil mengganti password.');

        return redirect()->route('dashboard.index')->with('success', 'Password berhasil diganti!');
    }
}
