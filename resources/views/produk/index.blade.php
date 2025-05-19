@extends('layouts.mantis')

@section('title', 'Halaman Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><strong><a href="{{ route('produk.index') }}">Produk</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('produk.create') }}" style="opacity: 0.5;">Tambah Data Produk</a></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Produk</h4>
            <a href="{{ route('produk.create') }}" class="btn btn-primary">Tambah Produk</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Harga Normal</th>
                            <th>Harga Grosir</th>
                            <th>Stok</th>
                            <th>ROP</th>
                            <th>Kategori</th>
                            <th>Satuan Utama</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produk as $index => $item)
                        <tr class="{{ $item->isStokDiBawahROP() ? 'table-danger' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if ($item->gambar)
                                <img src="{{ asset('storage/gambar_produk/' . $item->gambar) }}" alt="Gambar Produk" width="60" height="60" class="rounded-circle object-fit-cover">
                                @else
                                <span class="text-muted">Tidak Ada</span>
                                @endif
                            </td>
                            <td>{{ $item->nama_produk }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ number_format($item->harga_normal, 0, ',', '.') }}</td>
                            <td>{{ number_format($item->harga_grosir, 0, ',', '.') }}</td>
                            <td>
                                {{-- Tampilkan stok bertingkat --}}
                                {{ $item->stok_bertingkat }}
                                @if($item->isStokDiBawahROP())
                                <span class="badge bg-danger ms-2">
                                    <!-- Butuh Reorder Min: {{ max(0, ($item->rop - $item->stok) + 1) }} -->
                                </span>
                                @else
                                <span class="badge bg-success ms-2">Stok Aman</span>
                                @endif
                            </td>
                            <td>{{ $item->rop }}</td>
                            <td>{{ $item->kategori }}</td>
                            <td>{{ $item->satuan_utama ?? $item->satuan }}</td>
                            <td>
                                <a href="{{ route('produk.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $item->id }}">
                                    Hapus
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $item->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus produk {{ $item->nama_produk }}?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('produk.destroy', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>

                </table>
            </div> <!-- .table-responsive -->
        </div>
    </div>
</div>
@endsection