@extends('layouts.mantis')

@section('title')
Halaman Pegawai
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Pegawai</h4>
            <div>
                <a href="{{ route('pegawai.create') }}" class="btn btn-primary">Tambah Data</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th> <!-- Kolom foto -->
                        <th>Nama Pegawai</th>
                        <th>Jenis Kelamin</th>
                        <th>Umur</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pegawai as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if ($item->foto)
                            <img src="{{ asset('storage/foto_pegawai/' . $item->foto) }}" alt="Foto Pegawai" width="60" height="60" class="rounded-circle object-fit-cover">
                            @else
                            <span class="text-muted">Tidak Ada</span>
                            @endif
                        </td>
                        <td>{{ $item->nama_pegawai }}</td>
                        <td>{{ $item->jenis_kelamin }}</td>
                        <td>{{ $item->umur }}</td>
                        <td>{{ $item->tempat_lahir }}, {{ \Carbon\Carbon::parse($item->tanggal_lahir)->locale('id')->translatedFormat('d F Y') }}</td>
                        <td>{{ $item->alamat }}</td>
                        <td>
                            <a href="{{ route('pegawai.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
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
                                    Apakah Anda yakin ingin menghapus data pegawai {{ $item->nama_pegawai }}?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <!-- Form penghapusan pegawai -->
                                    <form action="{{ route('pegawai.destroy', $item->id) }}" method="POST" style="display:inline;">
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
        </div>
    </div>
</div>
@endsection