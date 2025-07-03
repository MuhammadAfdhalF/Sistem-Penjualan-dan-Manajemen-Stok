<script>
    document.addEventListener('DOMContentLoaded', function() {
            // Intl.NumberFormat untuk format tampilan mata uang (Rp)
            const formatter = new Intl.NumberFormat('id-ID');

            // Fungsi untuk memformat angka menjadi string mata uang (untuk tampilan di UI)
            function formatCurrency(num) {
                // Pastikan num adalah angka, jika tidak, konversi atau default ke 0
                if (typeof num !== 'number') num = parseFloat(num) || 0;
                // Gunakan toFixed(2) untuk memastikan 2 desimal, lalu format
                return formatter.format(num.toFixed(2));
            }

            // Fungsi untuk membersihkan string mata uang menjadi angka float murni (untuk perhitungan dan pengiriman ke backend)
            function cleanNumberStringForCalculationAndSubmission(str) {
                if (typeof str !== 'string') {
                    str = String(str);
                }
                // Hapus semua titik (pemisah ribuan)
                str = str.replace(/\./g, '');
                // Ganti koma (pemisah desimal) dengan titik
                str = str.replace(/,/g, '.');
                return str;
            }

            // Fungsi untuk mengurai string mata uang menjadi angka float
            function parseCurrencyToFloat(str) {
                if (!str) return 0;
                return parseFloat(cleanNumberStringForCalculationAndSubmission(str)) || 0;
            }

            // --- Elemen Form Utama ---
            const formTransaksi = document.getElementById('formTransaksi');
            const totalInput = document.getElementById('total');
            const dibayarInput = document.getElementById('dibayar');
            const kembalianInput = document.getElementById('kembalian');
            const jenisPelangganSelect = document.getElementById('jenis_pelanggan');
            const produkTable = document.getElementById('produkTable');


            // --- FUNGSI UTAMA PERHITUNGAN DAN RENDER ---

            // Render input jumlah bertingkat berdasarkan produk terpilih
            function renderJumlahBertingkatInputs(row) {
                const produkSelect = row.querySelector('.produk-select');
                const satuansJSON = produkSelect.selectedOptions[0]?.dataset.satuans || '[]';
                let satuanArr = [];

                try {
                    satuanArr = JSON.parse(satuansJSON);
                    // Urutkan satuan dari konversi terbesar ke terkecil
                    satuanArr.sort((a, b) => b.konversi_ke_satuan_utama - a.konversi_ke_satuan_utama);
                } catch (e) {
                    satuanArr = [];
                    console.error('Error parsing satuans JSON:', e);
                }

                let jumlahJsonInput = row.querySelector('.jumlah-json-input');
                let hargaJsonInput = row.querySelector('.harga-json-input');

                let jumlahData = {};
                let hargaData = {};

                try {
                    jumlahData = jumlahJsonInput && jumlahJsonInput.value ? JSON.parse(jumlahJsonInput.value) : {};
                    hargaData = hargaJsonInput && hargaJsonInput.value ? JSON.parse(hargaJsonInput.value) : {};
                } catch (e) {
                    console.error('Error parsing jumlah_json or harga_json from hidden inputs:', e);
                    jumlahData = {};
                    hargaData = {};
                }

                const container = row.querySelector('.jumlah-bertingkat-container');
                container.innerHTML = ''; // Bersihkan container

                satuanArr.forEach(satuan => {
                    const qty = jumlahData[satuan.id] ?? 0; // Ambil qty yang sudah ada atau 0

                    const div = document.createElement('div');
                    div.className = 'input-group input-group-sm mb-1 satuan-jumlah-row';
                    div.innerHTML = `
                <label class="input-group-text" style="min-width: 100px;">${satuan.nama_satuan}</label>
                <input type="number" class="form-control jumlah-per-satuan" min="0" step="any" value="${qty}" data-satuan-id="${satuan.id}">
            `;
                    container.appendChild(div);
                });

                updateSubtotal(row); // Panggil update subtotal setelah render input jumlah
            }

            // Update subtotal per baris berdasarkan jumlah bertingkat & harga per satuan dari server
            function updateSubtotal(row) {
                const produkSelect = row.querySelector('.produk-select');
                const produkId = produkSelect.value;
                const jenisPelanggan = jenisPelangganSelect.value;
                const subtotalInput = row.querySelector('.subtotal');
                const hargaInputHidden = row.querySelector('.harga-input');
                const jumlahJsonInputHidden = row.querySelector('.jumlah-json-input');
                const hargaJsonInputHidden = row.querySelector('.harga-json-input');
                const jumlahPerSatuanInputs = row.querySelectorAll('.jumlah-per-satuan');

                if (!produkId || !jenisPelanggan) {
                    subtotalInput.value = '';
                    hargaInputHidden.value = '';
                    jumlahJsonInputHidden.value = '';
                    hargaJsonInputHidden.value = '';
                    calculateGrandTotal();
                    return;
                }

                // Ambil harga dari server
                fetch(`/get-harga-produk-all?produk_id=${produkId}&jenis_pelanggan=${encodeURIComponent(jenisPelanggan)}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            console.warn('No price data found for product:', produkId, 'and type:', jenisPelanggan);
                            subtotalInput.value = '';
                            hargaInputHidden.value = '';
                            jumlahJsonInputHidden.value = '';
                            hargaJsonInputHidden.value = '';
                            calculateGrandTotal();
                            return;
                        }

                        let currentSubtotal = 0;
                        const hargaMap = data.hargaMap || {}; // Map satuan_id ke harga
                        const jumlahObj = {}; // Untuk disimpan ke jumlah_json
                        const hargaObj = {}; // Untuk disimpan ke harga_json (snapshot harga)

                        jumlahPerSatuanInputs.forEach(input => {
                            const satuanId = input.dataset.satuanId;
                            const qty = parseFloat(input.value) || 0; // Pastikan qty adalah float

                            jumlahObj[satuanId] = qty; // Simpan qty ke objek JSON

                            const hargaSatuan = parseFloat(hargaMap[satuanId]) || 0; // Pastikan harga satuan adalah float
                            hargaObj[satuanId] = hargaSatuan; // Simpan harga snapshot

                            currentSubtotal += hargaSatuan * qty;
                        });

                        subtotalInput.value = formatCurrency(currentSubtotal); // Tampilkan subtotal yang diformat
                        jumlahJsonInputHidden.value = JSON.stringify(jumlahObj);
                        hargaJsonInputHidden.value = JSON.stringify(hargaObj);

                        // Set harga utama ke input hidden harga[] (ambil harga satuan terbesar atau rata-rata jika perlu)
                        // Di sini, kita bisa ambil harga satuan terkecil (konversi 1) sebagai harga utama jika ada
                        const hargaUtamaSatuan = Object.keys(hargaObj).length > 0 ? hargaObj[Object.keys(hargaObj).find(id => {
                            const satuan = produkSelect.selectedOptions[0].dataset.satuans ? JSON.parse(produkSelect.selectedOptions[0].dataset.satuans).find(s => s.id == id) : null;
                            return satuan && satuan.konversi_ke_satuan_utama == 1;
                        })] : 0;
                        hargaInputHidden.value = hargaUtamaSatuan || 0; // Pastikan hargaInputHidden adalah angka murni

                        calculateGrandTotal();
                    })
                    .catch(err => {
                        console.error('Fetch error for product prices:', err);
                        subtotalInput.value = '';
                        hargaInputHidden.value = '';
                        jumlahJsonInputHidden.value = '';
                        hargaJsonInputHidden.value = '';
                        calculateGrandTotal();
                    });
            }

            // Hitung total keseluruhan dari semua subtotal
            function calculateGrandTotal() {
                let grandTotal = 0;
                document.querySelectorAll('.subtotal').forEach(input => {
                    const val = parseCurrencyToFloat(input.value); // Gunakan parseCurrencyToFloat
                    if (!isNaN(val)) grandTotal += val;
                });

                totalInput.value = formatCurrency(grandTotal); // Tampilkan total yang diformat

                calculateKembalian(); // Panggil perhitungan kembalian setelah total diperbarui
            }

            // Hitung kembalian
            function calculateKembalian() {
                const total = parseCurrencyToFloat(totalInput.value); // Gunakan parseCurrencyToFloat
                const dibayar = parseCurrencyToFloat(dibayarInput.value); // Gunakan parseCurrencyToFloat
                let kembalian = dibayar - total;

                // Pastikan kembalian tidak negatif jika dibayar kurang dari total
                if (kembalian < 0) kembalian = 0;

                kembalianInput.value = formatCurrency(kembalian); // Tampilkan kembalian yang diformat
            }

            // --- EVENT LISTENERS ---

            // Pasang event handler pada baris produk
            function attachEvents(row) {
                row.querySelector('.produk-select').addEventListener('change', function() {
                    renderJumlahBertingkatInputs(row);
                });

                row.addEventListener('input', function(e) {
                    // Jika input jumlah per satuan berubah
                    if (e.target.classList.contains('jumlah-per-satuan')) {
                        updateSubtotal(e.target.closest('.product-row')); // Pastikan target adalah baris produk
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
            jenisPelangganSelect.addEventListener('change', function() {
                document.querySelectorAll('.product-row').forEach(row => {
                    // Panggil renderJumlahBertingkatInputs untuk setiap baris produk
                    // Ini akan memicu updateSubtotal dan mengambil harga yang sesuai
                    renderJumlahBertingkatInputs(row);
                });
            });

            // Tambah baris produk baru
            document.getElementById('addRow').addEventListener('click', function() {
                const tbody = document.querySelector('#produkTable tbody');
                const templateRow = tbody.querySelector('.product-row'); // Ambil baris pertama sebagai template
                const newRow = templateRow.cloneNode(true);

                // Reset nilai input pada baris baru
                newRow.querySelector('.produk-select').selectedIndex = 0;
                newRow.querySelector('.jumlah-bertingkat-container').innerHTML = ''; // Kosongkan container jumlah
                newRow.querySelector('.subtotal').value = '';
                newRow.querySelector('.jumlah-json-input').value = '';
                newRow.querySelector('.harga-json-input').value = '';
                newRow.querySelector('.harga-input').value = '';

                tbody.appendChild(newRow);
                attachEvents(newRow); // Pasang event ke baris baru
                calculateGrandTotal(); // Hitung ulang total setelah menambah baris
            });

            // Format input uang saat mengetik di field dibayar
            dibayarInput.addEventListener('input', function() {
                // Bersihkan string untuk perhitungan, lalu format untuk tampilan
                this.value = formatCurrency(parseCurrencyToFloat(this.value));
                calculateGrandTotal(); // Panggil calculateGrandTotal untuk update kembalian
            });

            // Validasi sebelum submit (Client-Side Validation)
            formTransaksi.addEventListener('submit', function(e) {
                let isValid = true;

                // --- PENTING: BERSIHKAN NILAI INPUT SEBELUM SUBMIT ---
                // Ini memastikan Laravel menerima angka murni tanpa pemisah ribuan
                totalInput.value = cleanNumberStringForCalculationAndSubmission(totalInput.value);
                dibayarInput.value = cleanNumberStringForCalculationAndSubmission(dibayarInput.value);
                kembalianInput.value = cleanNumberStringForCalculationAndSubmission(kembalianInput.value);

                // Bersihkan juga nilai di input tersembunyi harga_json dan jumlah_json
                document.querySelectorAll('.product-row').forEach(function(row) {
                    let jumlahJsonInput = row.querySelector('.jumlah-json-input');
                    let hargaJsonInput = row.querySelector('.harga-json-input');

                    // Hapus formatting dari nilai-nilai di dalam JSON strings
                    if (jumlahJsonInput && jumlahJsonInput.value) {
                        try {
                            let jumlahArr = JSON.parse(jumlahJsonInput.value);
                            for (let id in jumlahArr) {
                                if (jumlahArr.hasOwnProperty(id)) {
                                    jumlahArr[id] = cleanNumberStringForCalculationAndSubmission(jumlahArr[id]);
                                }
                            }
                            jumlahJsonInput.value = JSON.stringify(jumlahArr);
                        } catch (e) {
                            console.error("Error cleaning jumlah_json:", e);
                        }
                    }
                    if (hargaJsonInput && hargaJsonInput.value) {
                        try {
                            let hargaArr = JSON.parse(hargaJsonInput.value);
                            for (let id in hargaArr) {
                                if (hargaArr.hasOwnProperty(id)) {
                                    hargaArr[id] = cleanNumberStringForCalculationAndSubmission(hargaArr[id]);
                                }
                            }
                            hargaJsonInput.value = JSON.stringify(hargaArr);
                        } catch (e) {
                            console.error("Error cleaning harga_json:", e);
                        }
                    }
                });
                // --- AKHIR PEMBESIHAN NILAI INPUT ---


                document.querySelectorAll('.produk-select').forEach(select => {
                    if (!select.value) { // Cek jika ada produk yang belum dipilih
                        alert('Harap pilih produk untuk semua baris');
                        isValid = false;
                        select.focus(); // Fokus ke select yang kosong
                        e.preventDefault(); // Hentikan submit
                        return false; // Hentikan foreach
                    }
                });

                if (!isValid) return; // Jika ada error produk belum dipilih, keluar

                const dibayar = parseCurrencyToFloat(dibayarInput.value); // Gunakan nilai yang sudah bersih
                const total = parseCurrencyToFloat(totalInput.value); // Gunakan nilai yang sudah bersih

                const metodePembayaran = metodePembayaranSelect.value;

                if (metodePembayaran !== 'payment_gateway' && dibayar < total) {
                    alert('Jumlah dibayar tidak boleh kurang dari total');
                    isValid = false;
                    dibayarInput.focus(); // Fokus ke input dibayar
                }

                if (!isValid) {
                    e.preventDefault(); // Hentikan submit jika validasi gagal
                }
            });

            // Auto-set jenis pelanggan jika pelanggan dipilih
            document.getElementById('pelanggan_id').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const jenis = selectedOption.getAttribute('data-jenis');

                if (jenis) {
                    jenisPelangganSelect.value = jenis;
                    // Panggil event 'change' pada jenis_pelanggan agar harga produk terupdate
                    jenisPelangganSelect.dispatchEvent(new Event('change'));
                } else {
                    // Jika 'Tanpa Pelanggan' dipilih, reset jenis_pelanggan
                    jenisPelangganSelect.value = '';
                    jenisPelangganSelect.dispatchEvent(new Event('change'));
                }
            });

            // Inisialisasi: Pasang event ke baris produk yang ada saat load
            // Dan render input jumlah bertingkat serta hitung total awal
            document.querySelectorAll('.product-row').forEach(row => {
                attachEvents(row);
                // renderJumlahBertingkatInputs(row); // Ini akan dipanggil saat produk dipilih
            });
            calculateGrandTotal(); // Panggil saat load untuk inisialisasi total

            // Jika ada produk yang sudah terpilih saat edit, pastikan ter-render
            document.querySelectorAll('.produk-select').forEach(select => {
                if (select.value) { // Jika ada produk yang sudah dipilih
                    renderJumlahBertingkatInputs(select.closest('.product-row'));
                }
            });
        }


    );
    // Tambahkan ini di bawah document.addEventListener('DOMContentLoaded', function() {
    const metodePembayaranSelect = document.getElementById('metode_pembayaran');

    metodePembayaranSelect.addEventListener('change', function() {
        const dibayarGroup = document.getElementById('dibayarGroup');
        const kembalianGroup = document.getElementById('kembalianGroup');

        if (this.value === 'payment_gateway') {
            dibayarGroup.style.display = 'none';
            kembalianGroup.style.display = 'none';
        } else {
            dibayarGroup.style.display = '';
            kembalianGroup.style.display = '';
        }
    });
</script>