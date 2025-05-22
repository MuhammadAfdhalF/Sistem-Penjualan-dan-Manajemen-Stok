<script>
document.addEventListener("DOMContentLoaded", function () {
    function hitungSubtotal(row) {
        const harga = parseFloat((row.querySelector('.harga')?.value || '0').replace(/\D/g, '')) || 0;
        const jumlah = parseFloat(row.querySelector('.jumlah')?.value || 0);
        const subtotal = harga * jumlah;
        row.querySelector('.subtotal').value = subtotal.toLocaleString('id-ID');
        hitungTotal();
    }

    function hitungTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            const val = parseFloat(input.value.replace(/\D/g, '')) || 0;
            total += val;
        });
        const totalDisplay = document.querySelector('#totalDisplay');
        if (totalDisplay) totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    // Event: jumlah berubah
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('jumlah')) {
            const row = e.target.closest('tr');
            hitungSubtotal(row);
        }
    });

    // Event: pilih produk
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('produk-select')) {
            const row = e.target.closest('tr');
            const selected = e.target.options[e.target.selectedIndex];
            const satuanSelect = row.querySelector('.satuan-select');
            const hargaInput = row.querySelector('.harga');
            const subtotalInput = row.querySelector('.subtotal');

            // Reset satuan & harga
            satuanSelect.innerHTML = `<option value="">Pilih Satuan</option>`;
            hargaInput.value = '';
            subtotalInput.value = '';

            // Ambil data dari attribute
            const satuanData = JSON.parse(selected.getAttribute('data-satuan') || '[]');
            const hargaData = JSON.parse(selected.getAttribute('data-harga') || '{}');

            // Isi satuan
            satuanData.forEach(s => {
                satuanSelect.innerHTML += `<option value="${s.id}">${s.nama_satuan}</option>`;
            });

            // Simpan harga ke row (biar bisa dipakai pas pilih satuan)
            row.dataset.hargaProduk = JSON.stringify(hargaData);
        }

        // Event: pilih satuan
        if (e.target.classList.contains('satuan-select')) {
            const row = e.target.closest('tr');
            const satuanId = e.target.value;
            const hargaData = JSON.parse(row.dataset.hargaProduk || '{}');
            const harga = hargaData[satuanId] ?? 0;

            row.querySelector('.harga').value = parseFloat(harga).toLocaleString('id-ID');
            hitungSubtotal(row);
        }
    });

    // Hapus baris
    document.addEventListener('click', function (e) {
        if (e.target.closest('.removeRow')) {
            e.target.closest('tr').remove();
            hitungTotal();
        }
    });

    // Tambah baris
    document.getElementById('addRow')?.addEventListener('click', function () {
        const tbody = document.querySelector('#produkTable tbody');
        const row = tbody.querySelector('tr').cloneNode(true);

        // Reset semua input dan select
        row.querySelectorAll('input').forEach(input => input.value = '');
        row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        row.dataset.hargaProduk = '';

        tbody.appendChild(row);
    });
});
</script>
