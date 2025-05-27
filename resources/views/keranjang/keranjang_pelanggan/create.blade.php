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
                            <th>Satuan</th>
                            <th>Harga (Rp)</th>
                            <th>
                                <div style="display:flex;flex-direction:column;gap:2px;">
                                    <span>Jumlah</span>
                                    <small style="font-weight:normal;color:#777">Bisa bertingkat</small>
                                </div>
                            </th>
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
                                <select name="satuan_id[]" class="form-select satuan-select" required>
                                    <option value="">Pilih Satuan</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control harga" readonly>
                                <input type="hidden" class="harga-satuan">
                            </td>
                            <td>
                                <div class="input-group jumlah-group">
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="0.01" step="0.01" value="1" required>
                                    <button type="button" class="btn btn-sm btn-outline-secondary addSubJumlah" title="Tambah satuan bertingkat">+</button>
                                </div>
                                <div class="sub-jumlah-list mt-1"></div>
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

        {{-- Debug Output: Hapus jika sudah benar --}}
        <hr>
        <div>
            <strong>DEBUG Produk & Satuan:</strong>
            <pre style="font-size:11px;background:#f8f9fa;border:1px solid #e2e2e2">
{!! json_encode($produks->map(function($p) {
    return [
        'id' => $p->id,
        'nama_produk' => $p->nama_produk,
        'satuans' => $p->satuans->map(function($s) {
            return ['id' => $s->id, 'nama_satuan' => $s->nama_satuan];
        }),
        'hargaProduks' => $p->hargaProduks->map(function($h){
            return [
                'satuan_id' => $h->satuan_id,
                'harga' => $h->harga
            ];
        }),
    ];
}), JSON_PRETTY_PRINT) !!}
            </pre>
        </div>
        {{-- End Debug --}}
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Ambil harga map dari option produk
    function getHargaMap(option) {
        if (!option) return {};
        try {
            return JSON.parse(option.dataset.harga);
        } catch {
            return {};
        }
    }
    // Update subtotal harga per row
    function updateHargaRow(row) {
        const hargaInput = row.querySelector('.harga');
        const hargaSatuanInput = row.querySelector('.harga-satuan');
        const jumlahInput = row.querySelector('.jumlah');
        const satuanSelect = row.querySelector('.satuan-select');
        const produkSelect = row.querySelector('.produk-select');
        const selectedOption = produkSelect.options[produkSelect.selectedIndex];
        let hargaMap = getHargaMap(selectedOption);
        const satuanId = satuanSelect.value;
        const hargaSatuan = hargaMap[satuanId] ? parseFloat(hargaMap[satuanId]) : 0;
        const jumlahUtama = jumlahInput.value ? parseFloat(jumlahInput.value) : 0;
        // Sub jumlah
        let subTotal = jumlahUtama;
        row.querySelectorAll('.sub-jumlah-list .input-group').forEach(function(sub) {
            const subSatuan = sub.querySelector('.sub-satuan');
            const subJumlah = sub.querySelector('input[type=number]');
            if (subSatuan && subJumlah && subJumlah.value) {
                subTotal += parseFloat(subJumlah.value);
            }
        });
        const subtotal = hargaSatuan * subTotal;
        hargaInput.value = hargaSatuan ? 'Rp ' + subtotal.toLocaleString('id-ID') : '';
        hargaSatuanInput.value = hargaSatuan;
    }
    // Populate satuan
    function handleProdukChange(row) {
        const produkSelect = row.querySelector('.produk-select');
        const satuanSelect = row.querySelector('.satuan-select');
        const hargaInput = row.querySelector('.harga');
        const hargaSatuanInput = row.querySelector('.harga-satuan');
        satuanSelect.innerHTML = '<option value="">Pilih Satuan</option>';
        hargaInput.value = '';
        hargaSatuanInput.value = '';
        const selectedOption = produkSelect.options[produkSelect.selectedIndex];
        let satuans = [];
        if (selectedOption && selectedOption.dataset.satuan) {
            satuans = JSON.parse(selectedOption.dataset.satuan);
        }
        satuans.forEach(satuan => {
            const opt = document.createElement('option');
            opt.value = satuan.id;
            opt.text = satuan.nama_satuan;
            satuanSelect.appendChild(opt);
        });
        // Remove all sub jumlah inputs
        row.querySelector('.sub-jumlah-list').innerHTML = '';
        updateHargaRow(row);
    }

    function handleSatuanChange(row) {
        updateHargaRow(row);
    }

    function handleJumlahChange(row) {
        updateHargaRow(row);
    }
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-select')) {
            handleProdukChange(e.target.closest('tr'));
        }
        if (e.target.classList.contains('satuan-select')) {
            handleSatuanChange(e.target.closest('tr'));
        }
        if (e.target.classList.contains('jumlah')) {
            handleJumlahChange(e.target.closest('tr'));
        }
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlah')) {
            handleJumlahChange(e.target.closest('tr'));
        }
        if (e.target.classList.contains('sub-satuan') || e.target.classList.contains('sub-jumlah')) {
            updateHargaRow(e.target.closest('tr'));
        }
    });
    // Tambah row dinamis
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#keranjangTable tbody');
        const row = tbody.querySelector('tr');
        const clone = row.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(el => {
            if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
                if (el.classList.contains('satuan-select')) {
                    el.innerHTML = '<option value="">Pilih Satuan</option>';
                }
            } else if (el.tagName === 'INPUT') {
                el.value = (el.type === 'number') ? 1 : '';
            }
        });
        clone.querySelector('.sub-jumlah-list').innerHTML = '';
        tbody.appendChild(clone);
    });
    // Remove row, minimal 1 row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            const row = e.target.closest('tr');
            const tbody = row.parentNode;
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
        }
    });
    // Tambah input jumlah satuan bertingkat
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('addSubJumlah')) {
            const row = e.target.closest('tr');
            const satuanSelect = row.querySelector('.satuan-select');
            const subList = row.querySelector('.sub-jumlah-list');
            // Ambil satuan lain dari option (selain yg utama)
            const satuans = Array.from(satuanSelect.options)
                .filter(opt => opt.value && opt.value !== satuanSelect.value)
                .map(opt => ({
                    id: opt.value,
                    nama: opt.text
                }));
            if (satuans.length === 0) {
                alert('Tidak ada satuan bertingkat lain.');
                return;
            }
            // Pilihan satuan bertingkat
            const newInput = document.createElement('div');
            newInput.className = 'input-group input-group-sm mb-1';
            newInput.innerHTML = `
                <select name="sub_satuan_id[]" class="form-select sub-satuan" style="max-width:100px" required>
                    <option value="">Satuan</option>
                    ${satuans.map(s=>`<option value="${s.id}">${s.nama}</option>`).join('')}
                </select>
                <input type="number" name="sub_jumlah[]" class="form-control sub-jumlah" min="0.01" step="0.01" value="1" style="max-width:80px" required>
                <button type="button" class="btn btn-outline-danger btn-sm delSubJumlah" tabindex="-1"><i class="ti ti-x"></i></button>
            `;
            subList.appendChild(newInput);
            updateHargaRow(row);
        }
        // Hapus sub jumlah row
        if (e.target.classList.contains('delSubJumlah')) {
            const row = e.target.closest('tr');
            e.target.closest('.input-group').remove();
            updateHargaRow(row);
        }
    });
    // Initial populate satuan (onload)
    window.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.produk-select').forEach(function(select) {
            if (select.value) {
                handleProdukChange(select.closest('tr'));
            }
        });
    });
</script>
@endsection