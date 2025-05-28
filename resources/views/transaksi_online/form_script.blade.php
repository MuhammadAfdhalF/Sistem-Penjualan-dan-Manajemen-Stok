<script>
    document.addEventListener("DOMContentLoaded", function() {
        let jenisPelanggan = getJenisPelangganAktif();

        // Helper: Ambil harga dari attribute option
        function getHargaMap(option) {
            if (!option) return {};
            try {
                return JSON.parse(option.dataset.harga);
            } catch {
                return {};
            }
        }

        // Helper: Ambil satuan dari attribute option
        function getSatuanList(option) {
            if (!option) return [];
            try {
                return JSON.parse(option.dataset.satuan);
            } catch {
                return [];
            }
        }

        // Helper: Jenis pelanggan aktif
        function getJenisPelangganAktif() {
            const pelangganSelect = document.getElementById('selectPelanggan');
            if (!pelangganSelect) return 'Individu';
            const selected = pelangganSelect.options[pelangganSelect.selectedIndex];
            return selected?.dataset.jenis || 'Individu';
        }

        // Render input jumlah per satuan saat produk dipilih
        function handleProdukChange(row) {
            const produkSelect = row.querySelector('.produk-select');
            const satuanJumlahList = row.querySelector('.satuan-jumlah-list');
            satuanJumlahList.innerHTML = '';

            const satuans = getSatuanList(produkSelect.options[produkSelect.selectedIndex]);

            // Ambil data jumlah_json dari hidden input di baris ini
            const jumlahJsonInput = row.querySelector('.jumlah-json');
            let jumlahData = {};
            try {
                jumlahData = JSON.parse(jumlahJsonInput.value);
            } catch {
                jumlahData = {};
            }

            satuans.forEach(satuan => {
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group input-group-sm mb-1 satuan-jumlah-row';

                // Isi nilai jumlah dari jumlahData, jika ada
                const jumlahNilai = jumlahData[satuan.id] ?? 0;

                wrapper.innerHTML = `
                <label class="input-group-text" style="min-width:80px">${satuan.nama_satuan}</label>
                <input type="number" class="form-control jumlah-per-satuan" data-satuan-id="${satuan.id}" min="0" step="0.01" value="${jumlahNilai}">
            `;
                satuanJumlahList.appendChild(wrapper);
            });

            updateSubtotal(row);
        }

        // Hitung subtotal satu row (mengacu jenis pelanggan aktif)
        function updateSubtotal(row) {
            const produkSelect = row.querySelector('.produk-select');
            const satuanInputs = row.querySelectorAll('.jumlah-per-satuan');
            const hargaMap = getHargaMap(produkSelect.options[produkSelect.selectedIndex]);
            const jenis = jenisPelanggan || getJenisPelangganAktif();
            let subtotal = 0;
            satuanInputs.forEach(input => {
                const satuanId = input.dataset.satuanId;
                const jumlah = parseFloat(input.value) || 0;
                const hargaObj = hargaMap[satuanId] || {};
                const harga = parseFloat(hargaObj[jenis]) || 0;
                subtotal += jumlah * harga;
            });
            row.querySelector('.subtotal').value = subtotal ? 'Rp ' + subtotal.toLocaleString('id-ID') : '';
            updateTotal();
        }

        // Hitung total semua baris
        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                const val = parseFloat((input.value || '').replace(/[^\d]/g, '')) || 0;
                total += val;
            });
            const totalDisplay = document.querySelector('#totalDisplay');
            if (totalDisplay) totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Serialize ke jumlah_json sebelum submit
        document.getElementById('formTransaksiOnline').addEventListener('submit', function() {
            document.querySelectorAll('.product-row').forEach(row => {
                let jumlahObj = {};
                row.querySelectorAll('.jumlah-per-satuan').forEach(input => {
                    const satuanId = input.dataset.satuanId;
                    const jumlah = parseFloat(input.value) || 0;
                    if (jumlah > 0) jumlahObj[satuanId] = jumlah;
                });
                row.querySelector('.jumlah-json').value = JSON.stringify(jumlahObj);
            });
        });

        // Tambah baris produk
        document.getElementById('addRow')?.addEventListener('click', function() {
            const tbody = document.querySelector('#produkTable tbody');
            const row = tbody.querySelector('tr');
            const clone = row.cloneNode(true);

            // Reset semua select & input di baris baru
            clone.querySelectorAll('select, input').forEach(el => {
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else if (el.type === 'number') el.value = 0;
                else el.value = '';
            });
            clone.querySelector('.satuan-jumlah-list').innerHTML = '';
            tbody.appendChild(clone);
        });

        // Hapus baris, minimal 1 row
        document.addEventListener('click', function(e) {
            if (e.target.closest('.removeRow')) {
                const row = e.target.closest('tr');
                const tbody = row.parentNode;
                if (tbody.querySelectorAll('tr').length > 1) row.remove();
                updateTotal();
            }
        });

        // Produk berubah: render input jumlah per satuan
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('produk-select')) {
                handleProdukChange(e.target.closest('tr'));
            }
        });

        // Jumlah per satuan berubah: update subtotal
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('jumlah-per-satuan')) {
                updateSubtotal(e.target.closest('tr'));
            }
        });

        // Jika pelanggan berubah: update semua subtotal produk
        document.getElementById('selectPelanggan')?.addEventListener('change', function() {
            jenisPelanggan = getJenisPelangganAktif();
            document.querySelectorAll('.product-row').forEach(row => updateSubtotal(row));
        });

        // On load: populate jumlah bertingkat jika sudah ada produk terisi
        document.querySelectorAll('.produk-select').forEach(function(select) {
            if (select.value) handleProdukChange(select.closest('tr'));
        });
    });
</script>