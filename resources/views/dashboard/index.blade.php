@extends('layouts.mantis')


@section('title')
Halaman Dashboard
@endsection

<head>
    <title>Halaman Dashboard</title>
</head>

@section('content')
<div class="container">
    {{-- Dashboard Info (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_info')
    </div>

    {{-- Dashboard Stok Barang (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_keuangan')

    </div>



    {{-- Dashboard Ringkasan Keuangan (Mockup placeholder) --}}
    <div class="border p-3 mb-4 rounded  text-muted" style="min-height: 150px;">
        @include('dashboard.d_stok')

    </div>

</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateRopBtn = document.getElementById('updateRopBtn');
        const updateStatus = document.getElementById('updateStatus');

        if (updateRopBtn && updateStatus) { // Pastikan elemen ada sebelum menambahkan event listener
            updateRopBtn.addEventListener('click', async function() { // Tambahkan 'async' di sini
                // Nonaktifkan tombol dan tampilkan status loading
                updateRopBtn.disabled = true;
                updateRopBtn.textContent = 'Memperbarui ROP...';
                updateStatus.classList.remove('hidden');
                updateStatus.textContent = 'Memulai proses pembaruan...';
                updateStatus.style.color = '#333';
                updateStatus.style.backgroundColor = '#e0e0e0';

                try {
                    // Lakukan panggilan AJAX ke endpoint Laravel Anda
                    const response = await fetch('/api/update-rop', {
                        method: 'POST',
                        headers: {
                            // Ambil CSRF token dari meta tag
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json' // Memberi tahu server kita menerima JSON
                        },
                        // Jika Anda perlu mengirim data ke backend, tambahkan 'body: JSON.stringify({ key: value })'
                    });

                    const data = await response.json(); // Parse response JSON

                    if (response.ok) { // Cek jika response status 2xx (sukses)
                        // Tampilkan pesan sukses dari backend atau pesan default
                        updateStatus.textContent = data.message || 'ROP berhasil diperbarui!';
                        updateStatus.style.color = 'green';
                        updateStatus.style.backgroundColor = '#d4edda'; // Warna hijau muda untuk sukses

                        // Refresh halaman setelah pembaruan sukses
                        // Ini akan memuat ulang data ROP yang baru dari server
                        setTimeout(() => {
                            location.reload();
                        }, 1000); // Refresh setelah 1 detik
                    } else {
                        // Tangani error dari server (misal: validasi gagal, error server)
                        const errorMessage = data.message || 'Terjadi kesalahan saat memperbarui ROP.';
                        console.error('Error response from server:', data);
                        updateStatus.textContent = errorMessage;
                        updateStatus.style.color = 'red';
                        updateStatus.style.backgroundColor = '#f8d7da'; // Warna merah muda untuk error
                    }

                } catch (error) {
                    // Tangani error jaringan atau error lain saat fetch
                    console.error('Error during ROP update fetch:', error);
                    updateStatus.textContent = 'Gagal terhubung ke server. Coba lagi.';
                    updateStatus.style.color = 'red';
                    updateStatus.style.backgroundColor = '#f8d7da'; // Warna merah muda untuk error
                } finally {
                    // Selalu reset tombol dan sembunyikan status setelah proses selesai (baik sukses/gagal)
                    // Jika halaman di-refresh, bagian ini mungkin tidak sempat dieksekusi atau terlihat
                    setTimeout(() => {
                        updateRopBtn.disabled = false;
                        updateRopBtn.textContent = 'Update ROP';
                        updateStatus.classList.add('hidden'); // Sembunyikan status
                        updateStatus.style.backgroundColor = ''; // Reset background
                    }, 2000); // Sembunyikan pesan status setelah 2 detik (jika tidak refresh)
                }
            });
        }
    });
</script>