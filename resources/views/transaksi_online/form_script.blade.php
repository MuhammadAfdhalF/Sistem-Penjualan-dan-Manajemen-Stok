<script>
    document.addEventListener("DOMContentLoaded", function() {
        const formatter = new Intl.NumberFormat('id-ID');

        function formatCurrency(num) {
            if (typeof num !== 'number') num = parseFloat(num) || 0;
            return formatter.format(num);
        }

        function parseCurrency(str) {
            if (!str) return 0;
            return parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;
        }

        function getJenisPelangganAktif() {
            const pelangganSelect = document.getElementById('selectPelanggan');
            if (!pelangganSelect) return 'Individu';
            const selected = pelangganSelect.options[pelangganSelect.selectedIndex];
            return selected?.dataset.jenis || 'Individu';
        }

        function renderJumlahBertingkatInputs(row) {
            const produkSelect = row.querySelector('.produk-select');
            const satuansJSON = produkSelect.selectedOptions[0]?.dataset.satuans || '[]';
            let satuanArr = [];
            try {
                satuanArr = JSON.parse(satuansJSON);
            } catch {
                satuanArr = [];
            }
            let jumlahJsonInput = row.querySelector('.jumlah-json-input');
            let hargaJsonInput = row.querySelector('.harga-json-input');
            let jumlahData = {};
            let hargaData = {};
            try {
                jumlahData = jumlahJsonInput ? JSON.parse(jumlahJsonInput.value) : {};
                hargaData = hargaJsonInput ? JSON.parse(hargaJsonInput.value) : {};
            } catch {}
            const container = row.querySelector('.jumlah-bertingkat-container');
            container.innerHTML = '';
            satuanArr.forEach(satuan => {
                const qty = jumlahData[satuan.id] ?? 0;
                const div = document.createElement('div');
                div.className = 'input-group input-group-sm mb-1 satuan-jumlah-row';
                div.innerHTML = `
                <label class="input-group-text" style="min-width: 100px;">${satuan.nama_satuan}</label>
                <input type="number" class="form-control jumlah-per-satuan" min="0" step="0.01" value="${qty}" data-satuan-id="${satuan.id}">
                `;
                container.appendChild(div);
            });
            updateSubtotal(row);
        }

        function updateSubtotal(row) {
            const produkId = row.querySelector('.produk-select').value;
            const jenisPelanggan = getJenisPelangganAktif();
            const jumlahInputs = row.querySelectorAll('.jumlah-per-satuan');
            if (!produkId || !jenisPelanggan) {
                row.querySelector('.subtotal').value = '';
                row.querySelector('.harga-input').value = '';
                row.querySelector('.harga-json-input').value = '';
                row.querySelector('.jumlah-json-input').value = '';
                calculateGrandTotal();
                return;
            }
            fetch(`/get-harga-produk-all?produk_id=${produkId}&jenis_pelanggan=${encodeURIComponent(jenisPelanggan)}`)
                .then(res => res.ok ? res.json() : Promise.reject(res.status))
                .then(data => {
                    if (!data.success) {
                        row.querySelector('.subtotal').value = '';
                        row.querySelector('.harga-input').value = '';
                        row.querySelector('.harga-json-input').value = '';
                        row.querySelector('.jumlah-json-input').value = '';
                        calculateGrandTotal();
                        return;
                    }
                    let subtotal = 0;
                    const hargaMap = data.hargaMap || {};
                    const jumlahObj = {};
                    const hargaObj = {};
                    jumlahInputs.forEach(input => {
                        const satuanId = input.dataset.satuanId;
                        const qty = parseFloat(input.value) || 0;
                        jumlahObj[satuanId] = qty;
                        const hargaSatuan = hargaMap[satuanId] ?? 0;
                        hargaObj[satuanId] = hargaSatuan;
                        subtotal += hargaSatuan * qty;
                    });
                    row.querySelector('.subtotal').value = subtotal ? formatCurrency(subtotal) : '';
                    row.querySelector('.jumlah-json-input').value = JSON.stringify(jumlahObj);
                    row.querySelector('.harga-json-input').value = JSON.stringify(hargaObj);
                    const hargaInput = row.querySelector('.harga-input');
                    const hargaUtama = Object.values(hargaObj).reduce((max, h) => h > max ? h : max, 0);
                    if (hargaInput) hargaInput.value = hargaUtama;
                    calculateGrandTotal();
                })
                .catch(() => {
                    row.querySelector('.subtotal').value = '';
                    row.querySelector('.harga-input').value = '';
                    row.querySelector('.harga-json-input').value = '';
                    row.querySelector('.jumlah-json-input').value = '';
                    calculateGrandTotal();
                });
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                const val = parseCurrency(input.value);
                if (!isNaN(val)) total += val;
            });
            document.getElementById('totalDisplay').innerText = 'Rp ' + formatCurrency(total);
        }

        function attachEvents(row) {
            row.querySelector('.produk-select').addEventListener('change', function() {
                renderJumlahBertingkatInputs(row);
            });
            row.addEventListener('input', function(e) {
                if (e.target.classList.contains('jumlah-per-satuan')) {
                    updateSubtotal(row);
                }
            });
            row.querySelector('.removeRow').addEventListener('click', function() {
                const rows = document.querySelectorAll('#produkTable tbody tr');
                if (rows.length > 1) {
                    row.remove();
                    calculateGrandTotal();
                } else {
                    alert('Minimal harus ada satu baris produk');
                }
            });
        }
        document.getElementById('selectPelanggan').addEventListener('change', function() {
            document.querySelectorAll('.product-row').forEach(row => {
                renderJumlahBertingkatInputs(row);
            });
        });
        document.getElementById('addRow').addEventListener('click', function() {
            const tbody = document.querySelector('#produkTable tbody');
            const template = tbody.querySelector('tr');
            const newRow = template.cloneNode(true);
            newRow.querySelector('.produk-select').selectedIndex = 0;
            newRow.querySelector('.jumlah-bertingkat-container').innerHTML = '';
            newRow.querySelector('.subtotal').value = '';
            newRow.querySelector('.jumlah-json-input').value = '';
            newRow.querySelector('.harga-json-input').value = '';
            newRow.querySelector('.harga-input').value = '';
            tbody.appendChild(newRow);
            attachEvents(newRow);
        });
        document.getElementById('formTransaksiOnline').addEventListener('submit', function(e) {
            document.querySelectorAll('.product-row').forEach(row => {
                const jumlahInputs = row.querySelectorAll('.jumlah-per-satuan');
                let jumlahObj = {},
                    hargaObj = {};
                jumlahInputs.forEach(input => {
                    const satuanId = input.dataset.satuanId;
                    const qty = parseFloat(input.value) || 0;
                    jumlahObj[satuanId] = qty;
                });
                row.querySelector('.jumlah-json-input').value = JSON.stringify(jumlahObj);
            });
        });
        document.querySelectorAll('.product-row').forEach(row => {
            attachEvents(row);
            renderJumlahBertingkatInputs(row); // Render saat load (untuk edit)
        });
    });
</script>