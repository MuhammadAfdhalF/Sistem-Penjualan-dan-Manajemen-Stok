<script>
    document.addEventListener('DOMContentLoaded', function() {
        function formatCurrency(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function parseCurrency(str) {
            return parseInt(str.replace(/[^0-9]/g, '')) || 0;
        }

        function updateHargaFromServer(row) {
            const produkId = row.querySelector('.produk-select')?.value;
            const satuanId = row.querySelector('.satuan-select')?.value;
            const jenisPelanggan = document.getElementById('jenis_pelanggan')?.value;

            if (!produkId || !satuanId || !jenisPelanggan) {
                row.querySelector('.harga').value = '';
                row.querySelector('.subtotal').value = '';
                calculateGrandTotal();
                return;
            }

            const hargaInput = row.querySelector('.harga');
            hargaInput.value = '...'; // loading indicator sementara

            fetch(`/get-harga-produk?produk_id=${produkId}&satuan_id=${satuanId}&jenis_pelanggan=${encodeURIComponent(jenisPelanggan)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.harga !== null && !isNaN(data.harga)) {
                        const harga = parseFloat(data.harga);
                        const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;

                        row.querySelector('.harga').value = formatCurrency(harga);
                        row.querySelector('.subtotal').value = formatCurrency(harga * jumlah);
                        calculateGrandTotal();
                    } else {
                        hargaInput.value = '';
                        row.querySelector('.subtotal').value = '';
                    }
                })
                .catch(() => {
                    hargaInput.value = '';
                    row.querySelector('.subtotal').value = '';
                });
        }

        function calculateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.subtotal').forEach(function(input) {
                total += parseCurrency(input.value);
            });

            document.getElementById('total').value = formatCurrency(total);

            const dibayar = parseCurrency(document.getElementById('dibayar').value);
            const kembalian = dibayar - total;
            document.getElementById('kembalian').value = formatCurrency(kembalian < 0 ? 0 : kembalian);
        }

        function attachEvents(row) {
            row.querySelector('.produk-select').addEventListener('change', function() {
                const produkId = this.value;
                const satuanSelect = row.querySelector('.satuan-select');
                satuanSelect.innerHTML = '<option value="">Pilih Satuan</option>';

                fetch(`/get-satuan-by-produk/${produkId}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res?.data?.length) {
                            res.data.forEach(item => {
                                satuanSelect.innerHTML += `<option value="${item.id}">${item.nama_satuan}</option>`;
                            });
                            row.querySelector('.jumlah').value = 1;
                            updateHargaFromServer(row);
                        }
                    });
            });

            row.querySelector('.satuan-select').addEventListener('change', function() {
                row.querySelector('.jumlah').value = 1;
                updateHargaFromServer(row);
            });

            row.querySelector('.jumlah').addEventListener('input', function() {
                updateHargaFromServer(row);
            });

            row.querySelector('.removeRow').addEventListener('click', function() {
                const rows = document.querySelectorAll('#produkTable tbody tr');
                if (rows.length > 1) {
                    this.closest('tr').remove();
                    calculateGrandTotal();
                } else {
                    alert('Minimal harus ada satu baris produk');
                }
            });
        }

        // Event global: jenis pelanggan diubah → refresh semua harga
        document.getElementById('jenis_pelanggan')?.addEventListener('change', function() {
            document.querySelectorAll('.product-row').forEach(row => {
                updateHargaFromServer(row);
            });
        });

        // Inisialisasi baris awal
        document.querySelectorAll('.product-row').forEach(row => {
            attachEvents(row);
        });

        // Tambah baris
        document.getElementById('addRow').addEventListener('click', function() {
            const tbody = document.querySelector('#produkTable tbody');
            const template = tbody.querySelector('tr');
            const newRow = template.cloneNode(true);

            newRow.querySelector('.produk-select').selectedIndex = 0;
            newRow.querySelector('.satuan-select').innerHTML = '<option value="">Pilih Satuan</option>';
            newRow.querySelector('.jumlah').value = 1;
            newRow.querySelector('.harga').value = '';
            newRow.querySelector('.subtotal').value = '';

            tbody.appendChild(newRow);
            attachEvents(newRow);
        });

        // Event format input dibayar
        document.getElementById('dibayar').addEventListener('input', function() {
            this.value = formatCurrency(parseCurrency(this.value));
            calculateGrandTotal();
        });

        // Validasi submit
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
    });
</script>