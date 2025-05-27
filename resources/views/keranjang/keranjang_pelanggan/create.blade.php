@extends('layouts.mantis')

@section('title', 'Tambah Keranjang')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Tambah Keranjang</h4>
        <a href="{{ route('keranjang.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('keranjang.store') }}" method="POST" id="formTambahKeranjang">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="keranjangTable">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah Bertingkat</th>
                            <th>Subtotal</th>
                            <th class="text-center" style="width: 60px">
                                <button type="button" class="btn btn-sm btn-success" id="addRow">
                                    <i class="ti ti-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="keranjang-row">
                            <td>
                                <select name="produk_id[]" class="form-select produk-select" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach ($produks as $produk)
                                    <option
                                        value="{{ $produk->id }}"
                                        data-satuan='@json($produk->satuans->map(fn($s)=>["id"=>$s->id,"nama_satuan"=>$s->nama_satuan]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)'
                                        data-harga='@json($produk->hargaProduks->mapWithKeys(fn($h)=>[$h->satuan_id=>$h->harga]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)'>
                                        {{ $produk->nama_produk }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="satuan-jumlah-list"></div>
                                <input type="hidden" name="jumlah_json[]" class="jumlah-json">
                            </td>
                            <td>
                                <input type="text" class="form-control subtotal" readonly>
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
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Tambah ke Keranjang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function getHargaMap(option) {
        if (!option) return {};
        try {
            return JSON.parse(option.dataset.harga);
        } catch {
            return {};
        }
    }

    function getSatuanList(option) {
        if (!option) return [];
        try {
            return JSON.parse(option.dataset.satuan);
        } catch {
            return [];
        }
    }

    // Saat produk dipilih, munculkan input jumlah per satuan
    function handleProdukChange(row) {
        const produkSelect = row.querySelector('.produk-select');
        const satuanJumlahList = row.querySelector('.satuan-jumlah-list');
        satuanJumlahList.innerHTML = '';
        const satuans = getSatuanList(produkSelect.options[produkSelect.selectedIndex]);
        satuans.forEach(satuan => {
            const wrapper = document.createElement('div');
            wrapper.className = 'input-group input-group-sm mb-1 satuan-jumlah-row';
            wrapper.innerHTML = `
                <label class="input-group-text" style="min-width:80px">${satuan.nama_satuan}</label>
                <input type="number" class="form-control jumlah-per-satuan" data-satuan-id="${satuan.id}" min="0" step="0.01" value="0">
            `;
            satuanJumlahList.appendChild(wrapper);
        });
        updateSubtotal(row);
    }

    // Hitung subtotal row
    function updateSubtotal(row) {
        const produkSelect = row.querySelector('.produk-select');
        const satuanInputs = row.querySelectorAll('.jumlah-per-satuan');
        const hargaMap = getHargaMap(produkSelect.options[produkSelect.selectedIndex]);
        let subtotal = 0;
        satuanInputs.forEach(input => {
            const satuanId = input.dataset.satuanId;
            const jumlah = parseFloat(input.value) || 0;
            const harga = parseFloat(hargaMap[satuanId]) || 0;
            subtotal += jumlah * harga;
        });
        row.querySelector('.subtotal').value = subtotal ? 'Rp ' + subtotal.toLocaleString('id-ID') : '';
    }

    // Serialize ke hidden jumlah_json sebelum submit
    document.getElementById('formTambahKeranjang').addEventListener('submit', function(e) {
        document.querySelectorAll('.keranjang-row').forEach(row => {
            let jumlahObj = {};
            row.querySelectorAll('.jumlah-per-satuan').forEach(input => {
                const satuanId = input.dataset.satuanId;
                const jumlah = parseFloat(input.value) || 0;
                if (jumlah > 0) jumlahObj[satuanId] = jumlah;
            });
            row.querySelector('.jumlah-json').value = JSON.stringify(jumlahObj);
        });
    });

    // Tambah baris
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#keranjangTable tbody');
        const row = tbody.querySelector('tr');
        const clone = row.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(el => {
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else if (el.tagName === 'INPUT') {
                if (el.type === 'number') el.value = 0;
                else el.value = '';
            }
        });
        clone.querySelector('.satuan-jumlah-list').innerHTML = '';
        tbody.appendChild(clone);
    });

    // Hapus baris, minimal 1 row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            const row = e.target.closest('tr');
            const tbody = row.parentNode;
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
        }
    });

    // Populate satuan jumlah saat produk berubah
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-select')) {
            handleProdukChange(e.target.closest('tr'));
        }
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlah-per-satuan')) {
            updateSubtotal(e.target.closest('tr'));
        }
    });

    // On load: populate jumlah bertingkat jika sudah ada produk terisi
    window.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.produk-select').forEach(function(select) {
            if (select.value) handleProdukChange(select.closest('tr'));
        });
    });
</script>
@endsection