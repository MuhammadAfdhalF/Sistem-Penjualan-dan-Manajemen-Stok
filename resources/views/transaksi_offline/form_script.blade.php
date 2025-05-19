<script>
    document.addEventListener('DOMContentLoaded', function() {
        function formatCurrency(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function parseCurrency(str) {
            return parseInt(str.replace(/[^0-9]/g, '')) || 0;
        }

        function updateRowCalculations(row) {
            const selectProduk = row.querySelector('.produk-select');
            const tipeHargaSelect = row.querySelector('.tipe-harga-select');
            const selectedOption = selectProduk.options[selectProduk.selectedIndex];
            if (!selectedOption) return;

            let harga = 0;
            if (tipeHargaSelect.value === 'normal') {
                harga = parseFloat(selectedOption.getAttribute('data-harga-normal')) || 0;
            } else if (tipeHargaSelect.value === 'grosir') {
                harga = parseFloat(selectedOption.getAttribute('data-harga-grosir')) || 0;
            }

            const jumlah = parseInt(row.querySelector('.jumlah').value) || 0;
            const subtotal = harga * jumlah;

            row.querySelector('.harga').value = formatCurrency(harga);
            row.querySelector('.subtotal').value = formatCurrency(subtotal);

            calculateGrandTotal();
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
                updateRowCalculations(row);
            });

            row.querySelector('.tipe-harga-select').addEventListener('change', function() {
                updateRowCalculations(row);
            });

            row.querySelector('.jumlah').addEventListener('input', function() {
                updateRowCalculations(row);
            });

            row.querySelector('.removeRow').addEventListener('click', function() {
                if (document.querySelectorAll('#produkTable tbody tr').length > 1) {
                    this.closest('tr').remove();
                    calculateGrandTotal();
                } else {
                    alert('Minimal harus ada satu baris produk');
                }
            });
        }

        // Inisialisasi event pada baris yang sudah ada
        document.querySelectorAll('.product-row').forEach(function(row) {
            attachEvents(row);
            updateRowCalculations(row);
        });

        // Tombol tambah baris produk
        document.getElementById('addRow').addEventListener('click', function() {
            const tbody = document.querySelector('#produkTable tbody');
            const newRow = tbody.querySelector('tr').cloneNode(true);

            newRow.querySelector('.produk-select').selectedIndex = 0;
            newRow.querySelector('.tipe-harga-select').value = 'normal';
            newRow.querySelector('.harga').value = '';
            newRow.querySelector('.jumlah').value = 1;
            newRow.querySelector('.subtotal').value = '';

            attachEvents(newRow);

            tbody.appendChild(newRow);
        });

        // Event untuk input dibayar (format currency dan hitung kembali total)
        document.getElementById('dibayar').addEventListener('input', function() {
            this.value = formatCurrency(parseCurrency(this.value));
            calculateGrandTotal();
        });

        // Validasi form submit
        document.getElementById('formTransaksi').addEventListener('submit', function(e) {
            let isValid = true;

            document.querySelectorAll('.produk-select').forEach(function(select) {
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