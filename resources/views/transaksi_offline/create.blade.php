@extends('layouts.mantis')

@section('title', 'Halaman Tambah Transaksi Offline')

<head>
    <title>Halaman Tambah Transaksi Offline</title>
</head>

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h4 class="card-title mb-2 mb-md-0">Form Tambah Transaksi</h4>
        <a href="{{ route('transaksi_offline.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('transaksi_offline.store') }}" method="POST" id="formTransaksi">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="kode_transaksi" class="form-label">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" id="kode_transaksi" class="form-control" value="{{ $kode_transaksi }}" readonly>
                </div>
                <div class="col-md-4">
                    <label for="tanggal" class="form-label">Tanggal Transaksi</label>
                    <input type="datetime-local" name="tanggal" id="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d\TH:i') }}" readonly>
                </div>

                <div class="col-md-4">
                    <label for="pelanggan_id" class="form-label">Pilih Pelanggan (Opsional)</label>
                    <select name="pelanggan_id" id="pelanggan_id" class="form-select">
                        <option value="">-- Tanpa Pelanggan --</option>
                        @foreach ($pelanggans as $pel)
                        <option value="{{ $pel->id }}" data-jenis="{{ $pel->jenis_pelanggan }}">{{ $pel->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="jenis_pelanggan" class="form-label">Jenis Pelanggan</label>
                    <select name="jenis_pelanggan" id="jenis_pelanggan" class="form-select" required>
                        <option value="">-- Pilih Jenis Pelanggan --</option>
                        <option value="Individu">Individu</option>
                        <option value="Toko Kecil">Toko Kecil</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="cash">Tunai</option>
                        <option value="payment_gateway">Payment Gateway</option>
                    </select>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="produkTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:180px">Produk</th>
                                <th style="min-width:300px">Jumlah</th>
                                <th style="min-width:120px">Subtotal (Rp)</th>
                                <th class="text-center" style="width: 60px;">
                                    <button type="button" class="btn btn-sm btn-success" id="addRow">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="product-row">
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($produk as $item)
                                        <option value="{{ $item->id }}" data-satuans='@json($item->satuans)'>{{ $item->nama_produk }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="jumlah-bertingkat-container"></div>
                                    <input type="hidden" name="jumlah_json[]" class="jumlah-json-input" required>
                                </td>
                                <td>
                                    <input type="text" name="subtotal[]" class="form-control subtotal text-end" readonly>
                                    <input type="hidden" name="harga[]" class="harga-input" />
                                    <input type="hidden" name="harga_json[]" class="harga-json-input" />
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <div class="col-md-4" id="paymentDetails">
                    <label for="total" class="form-label">Total</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="total" id="total" class="form-control text-end" readonly required>
                    </div>

                    <div class="form-group-dibayar mt-3">
                        <label for="dibayar" class="form-label">Dibayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="dibayar" id="dibayar" class="form-control text-end">
                        </div>
                    </div>

                    <div class="form-group-kembalian mt-3">
                        <label for="kembalian" class="form-label">Kembalian</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="kembalian" id="kembalian" class="form-control text-end" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="item_details" id="item_details">


            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
@include('transaksi_offline.form_script')

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const metodeSelect = document.getElementById('metode_pembayaran');
        const dibayarGroup = document.querySelector('.form-group-dibayar');
        const kembalianGroup = document.querySelector('.form-group-kembalian');
        const form = document.getElementById('formTransaksi');

        function togglePaymentFields() {
            const isGateway = metodeSelect.value === 'payment_gateway';
            dibayarGroup.style.display = isGateway ? 'none' : 'block';
            kembalianGroup.style.display = isGateway ? 'none' : 'block';
        }

        metodeSelect.addEventListener('change', togglePaymentFields);
        togglePaymentFields(); // initial run

        form.addEventListener('submit', async function(e) {
            const metode = metodeSelect.value;
            if (metode === 'payment_gateway') {
                e.preventDefault(); // prevent default submit

                // Ambil data produk
                const rows = document.querySelectorAll('.product-row');
                const itemDetails = [];

                rows.forEach(function(row, i) {
                    const select = row.querySelector('.produk-select');
                    const name = select.options[select.selectedIndex].text;
                    const price = parseFloat(row.querySelector('.harga-input')?.value || 0);
                    const jumlahJson = JSON.parse(row.querySelector('.jumlah-json-input')?.value || '{}');

                    let totalQty = 0;
                    for (const satuan in jumlahJson) {
                        totalQty += parseFloat(jumlahJson[satuan] || 0);
                    }

                    if (totalQty >= 1) {
                        itemDetails.push({
                            id: `item-${i + 1}`,
                            price: price,
                            quantity: totalQty,
                            name: name.substring(0, 50)
                        });
                    }
                });

                if (itemDetails.length === 0) {
                    alert("Mohon isi jumlah produk minimal 1.");
                    return;
                }

                // Set item_details hidden input
                document.getElementById('item_details').value = JSON.stringify(itemDetails);

                const formData = new FormData(form);
                const total = parseFloat(document.getElementById('total').value || 0);
                formData.append('total', total);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });

                    const result = await response.json();

                    if (result.snap_token) {
                        window.snap.pay(result.snap_token, {
                            onSuccess: function() {
                                window.location.href = "/transaksi-offline/sukses";
                            },
                            onPending: function() {
                                window.location.href = "/transaksi-offline/menunggu";
                            },
                            onError: function() {
                                alert("Terjadi kesalahan saat memproses pembayaran.");
                            },
                            onClose: function() {
                                alert("Pembayaran dibatalkan.");
                            }
                        });
                    } else {
                        alert(result.error || 'Gagal mendapatkan Snap Token.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Gagal memproses pembayaran.');
                }
            }
        });
    });
</script>
@endsection