@extends('layout.master')
@section('title', 'Tambah Penjualan')

@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-primary">🛒 Tambah Penjualan</h2>
        </div>
        <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary rounded-pill shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Kolom Kiri -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3 text-primary">
                            <i class="bi bi-box-seam me-2"></i> Pilih Produk
                        </h5>

                        {{-- Pilih Pelanggan --}}
                        <div class="mb-4">
                            <label for="pelanggan_id" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-person-circle me-1"></i> Pelanggan (opsional)
                            </label>
                            <select name="pelanggan_id" id="pelanggan_id" class="form-select shadow-sm">
                                <option value="">-- Umum --</option>
                                @foreach ($pelanggans as $p)
                                    <option value="{{ $p->pelanggan_id }}">{{ $p->nama_pelanggan }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pilih Produk --}}
                        <div class="mb-4">
                            <label for="produk" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-search me-1"></i> Cari Produk
                            </label>
                            <div class="input-group">
                                <select id="produk" class="form-select shadow-sm">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($produks as $produk)
                                        <option value="{{ $produk->produk_id }}"
                                            data-harga="{{ $produk->harga_jual }}"
                                            data-foto="{{ $produk->photo ? asset('storage/' . $produk->photo) : 'https://via.placeholder.com/80' }}">
                                            {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary px-4" id="addItem">
                                    <i class="bi bi-plus-lg"></i> Tambah
                                </button>
                            </div>
                        </div>

                        {{-- Tabel Produk --}}
                        <div class="table-responsive mt-4">
                            <table class="table table-hover align-middle text-center">
                                <thead class="bg-primary text-white rounded-top">
                                    <tr>
                                        <th width="80">Foto</th>
                                        <th>Produk</th>
                                        <th width="130">Harga</th>
                                        <th width="120">Jumlah</th>
                                        <th width="130">Subtotal</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="produkList"></tbody>
                            </table>

                            <div id="emptyState" class="text-center py-5">
                                <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0 mt-2">Belum ada produk dipilih</p>
                                <small class="text-muted">Pilih produk dari dropdown di atas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-semibold mb-3 text-primary">
                            <i class="bi bi-receipt-cutoff me-2"></i> Ringkasan Transaksi
                        </h5>

                        <div class="bg-light rounded p-3 mb-3 shadow-sm">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <input type="text" id="subtotalHarga"
                                    class="border-0 bg-transparent text-end fw-semibold" style="width: 150px;" readonly>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Diskon (<span id="persenDiskon">0%</span>)</span>
                                <input type="text" id="nominalDiskon"
                                    class="border-0 bg-transparent text-end text-danger fw-semibold"
                                    style="width: 150px;" readonly>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <input type="text" id="totalHarga"
                                    class="border-0 bg-transparent text-end fw-bold text-success fs-5"
                                    style="width: 150px;" readonly>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-tag-fill"></i> Diskon Rp 5.000 per Rp 100.000
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="metode" class="form-label fw-semibold text-secondary">Metode Pembayaran</label>
                            <select name="metode" id="metode" class="form-select shadow-sm" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash">Cash</option>
                                <option value="debit">Debit</option>
                                <option value="ewallet">E-Wallet</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_bayar" class="form-label fw-semibold text-secondary">Jumlah Bayar</label>
                            <input type="number" name="jumlah_bayar" id="jumlah_bayar"
                                class="form-control form-control-lg shadow-sm" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary">Kembalian</label>
                            <input type="text" id="kembalian"
                                class="form-control form-control-lg fw-bold text-primary shadow-sm" readonly>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm rounded-pill">
                            <i class="bi bi-check-circle-fill me-2"></i> Simpan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    body {
        background-color: #f9fafb;
    }

    .card {
        border-radius: 14px;
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .table thead {
        border-radius: 10px;
    }

    .table th, .table td {
        vertical-align: middle;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1px solid #dee2e6;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .btn {
        border-radius: 10px;
    }
</style>

    <script>
        let produkList = document.getElementById('produkList');
        let addItem = document.getElementById('addItem');
        let produkSelect = document.getElementById('produk');
        let subtotalHargaInput = document.getElementById('subtotalHarga');
        let persenDiskonSpan = document.getElementById('persenDiskon');
        let nominalDiskonInput = document.getElementById('nominalDiskon');
        let totalHargaInput = document.getElementById('totalHarga');
        let jumlahBayarInput = document.getElementById('jumlah_bayar');
        let kembalianInput = document.getElementById('kembalian');
        let emptyState = document.getElementById('emptyState');

        // Format rupiah
        function formatRupiah(angka) {
            return 'Rp ' + (angka ? angka.toLocaleString('id-ID') : '0');
        }

        // Toggle empty state
        function toggleEmptyState() {
            if (produkList.children.length === 0) {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
            }
        }

        // Tambah produk
        addItem.addEventListener('click', function() {
            let option = produkSelect.options[produkSelect.selectedIndex];
            if (!option.value) return;

            let produkId = option.value;
            let namaProduk = option.text;
            let harga = parseInt(option.getAttribute('data-harga'));
            let foto = option.getAttribute('data-foto');

            // Cek apakah produk sudah ada di tabel
            let existingRow = null;
            produkList.querySelectorAll('tr').forEach(row => {
                let existingProdukId = row.querySelector('input[name="produk_id[]"]').value;
                if (existingProdukId == produkId) {
                    existingRow = row;
                }
            });

            if (existingRow) {
                // Jika produk sudah ada, tambah jumlahnya
                let jumlahInput = existingRow.querySelector('.jumlah');
                let jumlahSekarang = parseInt(jumlahInput.value) || 0;
                jumlahInput.value = jumlahSekarang + 1;

                // Update subtotal
                let subtotal = harga * (jumlahSekarang + 1);
                existingRow.querySelector('.subtotal').innerText = formatRupiah(subtotal);
            } else {
                // Jika produk belum ada, tambah baris baru
                let row = `
                <tr>
                    <td><img src="${foto}" alt="${namaProduk}" width="60" class="rounded"></td>
                    <td>
                        ${namaProduk}
                        <input type="hidden" name="produk_id[]" value="${produkId}">
                    </td>
                    <td>${formatRupiah(harga)}</td>
                    <td>
                        <input type="number" name="jumlah_produk[]" value="1" min="1" class="form-control form-control-sm jumlah">
                    </td>
                    <td class="subtotal">${formatRupiah(harga)}</td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeItem">Hapus</button></td>
                </tr>`;
                produkList.insertAdjacentHTML('beforeend', row);
            }

            toggleEmptyState();
            hitungTotal();
        });

        // Hapus produk
        produkList.addEventListener('click', function(e) {
            if (e.target.classList.contains('removeItem')) {
                e.target.closest('tr').remove();
                toggleEmptyState();
                hitungTotal();
            }
        });

        // Update subtotal saat jumlah berubah
        produkList.addEventListener('input', function(e) {
            if (e.target.classList.contains('jumlah')) {
                let row = e.target.closest('tr');
                let harga = parseInt(row.querySelector('td:nth-child(3)').innerText.replace(/\D/g, '')) || 0;
                let jumlah = parseInt(e.target.value) || 0;
                let subtotal = harga * jumlah;
                row.querySelector('.subtotal').innerText = formatRupiah(subtotal);
                hitungTotal();
            }
        });

        // Hitung total
        function hitungTotal() {
            let subtotal = 0;
            produkList.querySelectorAll('tr').forEach(row => {
                let rowSubtotal = parseInt(row.querySelector('.subtotal').innerText.replace(/\D/g, '')) || 0;
                subtotal += rowSubtotal;
            });

            subtotalHargaInput.value = formatRupiah(subtotal);

            // Diskon Rp 5.000 per 100rb
            let kelipatan100rb = Math.floor(subtotal / 100000);
            let nominalDiskon = kelipatan100rb * 5000;
            nominalDiskon = Math.min(nominalDiskon, subtotal);

            // Persen diskon (dibulatkan tanpa koma)
            let persenDiskon = subtotal > 0 ? Math.floor((nominalDiskon / subtotal) * 100) : 0;

            // Tampilkan
            persenDiskonSpan.innerText = persenDiskon + '%';
            nominalDiskonInput.value = formatRupiah(nominalDiskon);

            let totalSetelahDiskon = subtotal - nominalDiskon;
            totalHargaInput.value = formatRupiah(totalSetelahDiskon);

            let bayar = parseInt(jumlahBayarInput.value) || 0;
            let kembali = bayar - totalSetelahDiskon;
            kembalianInput.value = formatRupiah(kembali >= 0 ? kembali : 0);
        }

        jumlahBayarInput.addEventListener('input', hitungTotal);

        // Initialize
        toggleEmptyState();
        hitungTotal();
    </script>
@endsection
