<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\TransaksiOffline;
use Illuminate\Http\Request;

class KeuanganController extends Controller
{
    /**
     * Tampilkan daftar semua catatan keuangan.
     */
    public function index()
    {
        $keuangans = Keuangan::with('transaksi')->latest()->get();
        return view('keuangan.index', compact('keuangans'));
    }

    /**
     * Tampilkan form tambah data keuangan (manual).
     */
    public function create()
    {
        $transaksis = TransaksiOffline::latest()->get();
        return view('keuangan.create', compact('transaksis'));
    }

    /**
     * Simpan data keuangan baru (manual input).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => 'nullable|exists:transaksi_offline,id',
            'tanggal'      => 'required|date',
            'jenis'        => 'required|in:pemasukan,pengeluaran',
            'nominal'      => 'required|numeric|min:0',
            'keterangan'   => 'nullable|string',
        ]);

        // Tambah default sumber = 'manual'
        $validated['sumber'] = 'manual';

        Keuangan::create($validated);

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil disimpan.');
    }

    /**
     * Tampilkan form edit data keuangan.
     */
    public function edit(Keuangan $keuangan)
    {
        $transaksis = TransaksiOffline::latest()->get();
        return view('keuangan.edit', compact('keuangan', 'transaksis'));
    }

    /**
     * Update data keuangan.
     */
    public function update(Request $request, Keuangan $keuangan)
    {
        // Optional: Lindungi agar keuangan otomatis dari transaksi tidak bisa diedit manual
        if ($keuangan->sumber !== 'manual') {
            return redirect()->route('keuangan.index')->with('error', 'Data keuangan otomatis tidak dapat diedit.');
        }

        $validated = $request->validate([
            'transaksi_id' => 'nullable|exists:transaksi_offline,id',
            'tanggal'      => 'required|date',
            'jenis'        => 'required|in:pemasukan,pengeluaran',
            'nominal'      => 'required|numeric|min:0',
            'keterangan'   => 'nullable|string',
        ]);

        $keuangan->update($validated);

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil diperbarui.');
    }

    /**
     * Hapus data keuangan.
     */
    public function destroy(Keuangan $keuangan)
    {
        // Optional: Lindungi data keuangan otomatis agar tidak dihapus manual
        if ($keuangan->sumber !== 'manual') {
            return redirect()->route('keuangan.index')->with('error', 'Data keuangan otomatis tidak dapat dihapus.');
        }

        $keuangan->delete();

        return redirect()->route('keuangan.index')->with('success', 'Data keuangan berhasil dihapus.');
    }
}
