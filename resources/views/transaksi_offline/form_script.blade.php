<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formatter = new Intl.NumberFormat('id-ID');

        function formatCurrency(num) {
            if (typeof num !== 'number') num = parseFloat(num) || 0;
            return formatter.format(num);
        }

        function parseCurrency(str) {
            if (!str) return 0;
            return parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;
        }

        // Render input jumlah bertingkat berdasarkan produk terpilih
        function renderJumlahBertingkatInputs(row) {
            const produkSelect = row.querySelector('.produk-select');
            const satuansJSON = produkSelect.selectedOptions[0]?.dataset.satuans || '[]';
            let satuanArr = [];

            try {
                satuanArr = JSON.parse(satuansJSON);
            } catch (e) {
                satuanArr = [];
                console.error('Error parsing satuans JSON:', e);
            }

            // Ambil data JSON jumlah dan harga dari input hidden (jika ada)
            let jumlahJsonInput = row.querySelector('.jumlah-json-input');
            let hargaJsonInput = row.querySelector('.harga-json-input');

            let jumlahData = {};
            let hargaData = {};

            try {
                jumlahData = jumlahJsonInput ? JSON.parse(jumlahJsonInput.value) : {};
                hargaData = hargaJsonInput ? JSON.parse(hargaJsonInput.value) : {};
            } catch (e) {
                console.error('Error parsing jumlah_json or harga_json:', e);
            }

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

        // Update subtotal per baris berdasarkan jumlah bertingkat & harga per satuan dari server
        function updateSubtotal(row) {
            const produkId = row.querySelector('.produk-select').value;
            const jenisPelanggan = document.getElementById('jenis_pelanggan').value;
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
                .then(res => {
                    if (!res.ok) throw new Error(`Response status ${res.status}`);
                    return res.json();
                })
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

                    row.querySelector('.subtotal').value = formatCurrency(subtotal);
                    row.querySelector('.jumlah-json-input').value = JSON.stringify(jumlahObj);
                    row.querySelector('.harga-json-input').value = JSON.stringify(hargaObj);

                    // Set harga utama ke input hidden harga[] (ambil harga satuan terbesar)
                    const hargaInput = row.querySelector('.harga-input');
                    const hargaUtama = Object.values(hargaObj).reduce((max, h) => h > max ? h : max, 0);
                    hargaInput.value = hargaUtama;

                    calculateGrandTotal();
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    row.querySelector('.subtotal').value = '';
                    row.querySelector('.jumlah-json-input').value = '';
                    row.querySelector('.harga-json-input').value = '';
                    row.querySelector('.harga-input').value = '';
                    calculateGrandTotal();
                });
        }

        // Hitung total keseluruhan dari semua subtotal
        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(input => {
                const val = parseCurrency(input.value);
                if (!isNaN(val)) total += val;
            });

            document.getElementById('total').value = total ? formatCurrency(total) : '0';

            const dibayar = parseCurrency(document.getElementById('dibayar').value);
            let kembalian = dibayar - total;
            if (kembalian < 0) kembalian = 0;
            document.getElementById('kembalian').value = formatCurrency(kembalian);
        }

        // Pasang event handler pada baris produk
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

        // Event saat jenis pelanggan berubah
        document.getElementById('jenis_pelanggan').addEventListener('change', function() {
            document.querySelectorAll('.product-row').forEach(row => {
                renderJumlahBertingkatInputs(row);
            });
        });

        // Tambah baris produk baru
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

        // Format input uang saat mengetik di field dibayar
        document.getElementById('dibayar').addEventListener('input', function() {
            this.value = formatCurrency(parseCurrency(this.value));
            calculateGrandTotal();
        });

        // Validasi sebelum submit
        document.getElementById('formTransaksi').addEventListener('submit', function(e) {
            let isValid = true;

            document.querySelectorAll('.produk-select').forEach(select => {
                if (select.selectedIndex === 0) {
                    alert('Harap pilih produk untuk semua baris');
                    isValid = false;
                }
            });

            const dibayar = parseCurrency(document.getElementById('dibayar').value);
            const total = parseCurrency(document.getElementById('total').value);

            if (dibayar < total) {
                alert('Jumlah dibayar tidak boleh kurang dari total');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Auto-set jenis pelanggan jika pelanggan dipilih
        document.getElementById('pelanggan_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const jenis = selectedOption.getAttribute('data-jenis');

            if (jenis) {
                document.getElementById('jenis_pelanggan').value = jenis;
                document.getElementById('jenis_pelanggan').dispatchEvent(new Event('change'));
            }
        });

        // Pasang event ke baris produk yang ada saat load
        document.querySelectorAll('.product-row').forEach(row => {
            attachEvents(row);
            renderJumlahBertingkatInputs(row); // Render jumlah bertingkat langsung saat load (penting utk edit)
        });
    });
</script>