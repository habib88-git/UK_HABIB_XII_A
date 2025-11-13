@extends('layout.master')
@section('title', 'Tambah Penjualan')

@section('content')
    <div class="container py-4">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1 text-primary">🛒 Tambah Penjualan</h2>
                <p class="text-muted mb-0">Tambah transaksi penjualan baru</p>
            </div>
            <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary rounded-pill shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>

        {{-- Notifikasi Error --}}
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

        <form action="{{ route('penjualan.store') }}" method="POST" id="penjualanForm">
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
                                <div class="nice-box pelanggan-box">
                                    <select name="pelanggan_id" id="pelanggan_id">
                                        <option value="">-- Umum (Tanpa Diskon) --</option>
                                        @foreach ($pelanggans as $p)
                                            <option value="{{ $p->pelanggan_id }}">{{ $p->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="diskonInfoBox" class="mt-2 p-3 rounded" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-tag-fill text-success me-2 fs-5"></i>
                                        <div>
                                            <div class="fw-bold text-success">Diskon Aktif!</div>
                                            <small class="text-muted">Dapatkan diskon 5% untuk belanja ≥ Rp 100.000</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Input Barcode --}}
                            <div class="mb-4">
                                <label for="barcodeInput" class="form-label fw-semibold text-secondary">
                                    <i class="bi bi-upc-scan me-1"></i> Scan Barcode
                                </label>
                                <div class="nice-box input-box d-flex align-items-center">
                                    <input type="text" id="barcodeInput" class="nice-input"
                                        placeholder="Scan atau ketik barcode produk" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary ms-2" id="clearBarcode">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Scan barcode produk untuk menambahkannya ke keranjang
                                </small>
                            </div>

                            {{-- Pencarian dan Filter Produk --}}
                            <div class="mb-4">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="searchProduk" class="form-label fw-semibold text-secondary">
                                            <i class="bi bi-search me-1"></i> Cari Produk
                                        </label>
                                        <div class="nice-box input-box">
                                            <input type="text" id="searchProduk" class="nice-input"
                                                placeholder="Ketik nama produk...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="filterKategori" class="form-label fw-semibold text-secondary">
                                            <i class="bi bi-filter me-1"></i> Filter Kategori
                                        </label>
                                        <div class="nice-box">
                                            <select id="filterKategori">
                                                <option value="">Semua Kategori</option>
                                                @foreach ($kategoris as $kategori)
                                                    <option value="{{ $kategori->kategori_id }}">{{ $kategori->nama_kategori }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Daftar Produk --}}
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-semibold text-secondary mb-0">
                                        <i class="bi bi-list-ul me-1"></i> Daftar Produk
                                    </label>
                                    <span class="badge bg-primary" id="produkCount">0 produk</span>
                                </div>

                                <div class="produk-container" style="max-height: 300px; overflow-y: auto;">
                                    <div id="produkGrid" class="row g-2">
                                        <!-- Produk akan ditampilkan di sini -->
                                    </div>
                                    <div id="produkEmptyState" class="text-center py-4">
                                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-0 mt-2">Tidak ada produk ditemukan</p>
                                        <small class="text-muted">Coba ubah kata kunci pencarian atau filter kategori</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Produk yang Dipilih --}}
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-semibold text-primary mb-0">
                                        <i class="bi bi-cart-check me-2"></i> Keranjang Belanja
                                    </h6>
                                    <span class="badge bg-success" id="cartCount">0 item</span>
                                </div>

                                <div class="table-responsive rounded border">
                                    <table class="table table-hover align-middle text-center mb-0">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th width="80" class="rounded-start">Foto</th>
                                                <th class="text-start">Produk</th>
                                                <th width="100">Satuan</th>
                                                <th width="130">Harga</th>
                                                <th width="120">Jumlah</th>
                                                <th width="130">Subtotal</th>
                                                <th width="80" class="rounded-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="produkList" class="border-top-0"></tbody>
                                    </table>
                                </div>

                                <div id="emptyState" class="text-center py-5">
                                    <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mb-0 mt-2">Belum ada produk dipilih</p>
                                    <small class="text-muted">Pilih produk dari daftar di atas</small>
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
                                    <span id="subtotalHarga" class="fw-semibold">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="diskonSection"
                                    style="display: none;">
                                    <span class="text-muted">
                                        <span class="badge bg-success me-1">5%</span> Diskon
                                    </span>
                                    <span id="nominalDiskon" class="fw-semibold text-danger">- Rp 0</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total</span>
                                    <span id="totalHarga" class="fw-bold text-success fs-5">Rp 0</span>
                                </div>
                                <div id="diskonNote" class="alert alert-success mt-3 mb-0 py-2 px-3"
                                    style="display: none;">
                                    <small class="d-flex align-items-center mb-0">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <span>Anda hemat <strong id="diskonHemat">Rp 0</strong>!</span>
                                    </small>
                                </div>
                            </div>

                            {{-- Metode Pembayaran --}}
                            <div class="mb-4">
                                <label for="metode" class="form-label fw-semibold text-secondary">Metode
                                    Pembayaran</label>
                                <div class="nice-box metode-box">
                                    <select name="metode" id="metode" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="cash">💵 Cash</option>
                                        <option value="qris">📱 QRIS</option>
                                    </select>
                                </div>
                            </div>

                            {{-- QRIS Section --}}
                            <div id="qrisSection" class="mb-4" style="display: none;">
                                <div class="card border-primary">
                                    <div class="card-body text-center p-3">
                                        <h6 class="fw-bold text-primary mb-2">
                                            <i class="bi bi-qr-code-scan me-2"></i>Pembayaran QRIS
                                        </h6>
                                        <div id="qrisBarcodeContainer" class="mb-2">
                                            <!-- QR Code akan muncul di sini -->
                                        </div>
                                        <div class="fw-bold text-success fs-6" id="qrisAmountDisplay">Rp 0</div>
                                        <small class="text-muted">Scan QR code untuk pembayaran</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" id="cashSection">
                                <label for="jumlah_bayar" class="form-label fw-semibold text-secondary">Jumlah
                                    Bayar</label>
                                <div class="nice-box input-box">
                                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" min="0"
                                        class="nice-input" required>
                                </div>
                            </div>

                            <div class="mb-4" id="kembalianSection">
                                <label class="form-label fw-semibold text-secondary">Kembalian</label>
                                <div class="nice-box input-box">
                                    <input type="text" id="kembalian"
                                        class="nice-input fw-bold text-primary" readonly>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm rounded-pill py-3">
                                <i class="bi bi-check-circle-fill me-2"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- 🔥 MODAL NOTIFIKASI SUKSES --}}
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Transaksi Berhasil!</h4>
                        <p class="text-muted mb-4">Data penjualan berhasil disimpan ke database</p>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg rounded-pill" id="cetakStrukBtn">
                                <i class="bi bi-printer me-2"></i> Oke, Cetak Struk
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill" id="okeSajaBtn">
                                <i class="bi bi-check-lg me-2"></i> Oke
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🔥 MODAL QRIS BESAR --}}
        <div class="modal fade" id="qrisModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-primary">
                            <i class="bi bi-qr-code-scan me-2"></i>Pembayaran QRIS
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <p class="text-muted mb-2">Scan QR Code berikut untuk melakukan pembayaran</p>
                            <div class="fw-bold fs-4 text-success" id="qrisAmount">Rp 0</div>
                        </div>

                        <div id="qrCodeContainer" class="mb-3 p-4 bg-light rounded">
                            <!-- QR Code akan ditampilkan di sini -->
                        </div>

                        <div class="alert alert-info border-0">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Pembayaran akan diproses otomatis setelah scan QRIS
                            </small>
                        </div>

                        <button type="button" class="btn btn-success w-100 rounded-pill py-2" id="confirmQRIS">
                            <i class="bi bi-check-circle me-2"></i> Konfirmasi Pembayaran Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table th {
            border-bottom: none;
            font-weight: 600;
            padding: 12px 8px;
        }

        .table td {
            padding: 12px 8px;
            vertical-align: middle;
        }

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 0.375rem;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 0.375rem;
        }

        .jumlah {
            text-align: center;
            width: 70px;
            margin: 0 auto;
        }

        .sticky-top {
            z-index: 10;
        }

        .btn-remove {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        #emptyState, #produkEmptyState {
            display: none;
        }

        /* 🔥 STYLING UTAMA UNTUK SEMUA ELEMEN INPUT DAN SELECT */
        .nice-box {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 8px 16px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            width: 100%;
        }

        .nice-box:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border-color: #adb5bd;
        }

        .nice-box:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        /* 🔥 STYLING UNTUK SEMUA SELECT */
        .nice-box select {
            background-color: transparent !important;
            box-shadow: none !important;
            height: 42px;
            font-size: 15px;
            border: none !important;
            padding-left: 0;
            padding-right: 0;
            width: 100%;
            outline: none;
            cursor: pointer;
            color: #495057;
        }

        .nice-box select:focus {
            outline: none !important;
        }

        /* 🔥 STYLING UNTUK SEMUA INPUT */
        .nice-input {
            background-color: transparent !important;
            box-shadow: none !important;
            border: none !important;
            padding: 8px 0;
            height: 42px;
            font-size: 15px;
            width: 100%;
            outline: none;
        }

        .nice-input:focus {
            outline: none !important;
        }

        .pelanggan-box,
        .produk-box {
            margin-bottom: 16px;
        }

        .metode-box,
        .input-box {
            margin-bottom: 8px;
        }

        #addItem {
            width: 100%;
            padding: 10px;
            font-weight: 600;
        }

        .form-label {
            margin-bottom: 8px;
            font-size: 14px;
            color: #495057;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
        }

        .pelanggan-box,
        .produk-box,
        .metode-box,
        .input-box {
            min-height: 58px;
            display: flex;
            align-items: center;
        }

        .pelanggan-box select,
        .produk-box select,
        .metode-box select,
        .input-box .nice-input {
            flex: 1;
        }

        /* Info Box Diskon */
        #diskonInfoBox {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px dashed #28a745;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Badge Diskon */
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
        }

        /* Alert Diskon */
        #diskonNote {
            animation: fadeIn 0.3s ease-out;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
            border-left: 4px solid #28a745 !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* QRIS Section Styles */
        #qrisSection .card {
            border: 2px solid #0d6efd;
            background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
        }

        #qrisBarcodeContainer img {
            max-width: 150px;
            height: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 5px;
            background: white;
        }

        /* Modal Styles */
        .modal {
            z-index: 9999 !important;
        }

        .modal-backdrop {
            z-index: 9998 !important;
        }

        .modal-dialog {
            margin: 1.75rem auto !important;
            max-width: 500px !important;
        }

        .modal-content {
            border-radius: 20px !important;
        }

        .modal.fade .modal-dialog {
            transition: transform .3s ease-out;
            transform: translateY(-50px);
        }

        .modal.show .modal-dialog {
            transform: translateY(0) !important;
        }

        /* Barcode input styling */
        #barcodeInput {
            font-family: monospace;
            letter-spacing: 1px;
        }

        /* Produk Card Styles */
        .produk-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
            cursor: pointer;
        }

        .produk-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd;
        }

        .produk-card.selected {
            border-color: #0d6efd;
            background-color: #f0f8ff;
        }

        .produk-card img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .produk-card .produk-nama {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #333;
        }

        .produk-card .produk-harga {
            font-weight: 700;
            color: #28a745;
            font-size: 0.9rem;
        }

        .produk-card .produk-stok {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .produk-card .produk-kategori {
            font-size: 0.7rem;
            background: #f8f9fa;
            color: #495057;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .produk-container {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }

        .produk-container::-webkit-scrollbar {
            width: 6px;
        }

        .produk-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .produk-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .produk-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Style untuk jumlah input di tabel */
        .table .form-control {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.875rem;
        }

        .table .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
        }

        @media (max-width: 768px) {
            .nice-box {
                padding: 6px 12px;
            }

            .nice-box select,
            .nice-input {
                height: 38px;
                font-size: 14px;
            }

            #qrisBarcodeContainer img {
                max-width: 120px;
            }

            .produk-card {
                padding: 8px;
            }

            .produk-card img {
                width: 50px;
                height: 50px;
            }
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
<script>
    // 🔥 VARIABEL UTAMA
    let produkList = document.getElementById('produkList');
    let produkGrid = document.getElementById('produkGrid');
    let barcodeInput = document.getElementById('barcodeInput');
    let clearBarcodeBtn = document.getElementById('clearBarcode');
    let pelangganSelect = document.getElementById('pelanggan_id');
    let metodeSelect = document.getElementById('metode');
    let subtotalHargaSpan = document.getElementById('subtotalHarga');
    let nominalDiskonSpan = document.getElementById('nominalDiskon');
    let totalHargaSpan = document.getElementById('totalHarga');
    let jumlahBayarInput = document.getElementById('jumlah_bayar');
    let kembalianInput = document.getElementById('kembalian');
    let emptyState = document.getElementById('emptyState');
    let produkEmptyState = document.getElementById('produkEmptyState');
    let diskonSection = document.getElementById('diskonSection');
    let diskonNote = document.getElementById('diskonNote');
    let diskonInfoBox = document.getElementById('diskonInfoBox');
    let diskonHemat = document.getElementById('diskonHemat');
    let cashSection = document.getElementById('cashSection');
    let kembalianSection = document.getElementById('kembalianSection');
    let qrisSection = document.getElementById('qrisSection');
    let qrisBarcodeContainer = document.getElementById('qrisBarcodeContainer');
    let qrisAmountDisplay = document.getElementById('qrisAmountDisplay');
    let searchProdukInput = document.getElementById('searchProduk');
    let filterKategoriSelect = document.getElementById('filterKategori');
    let produkCountSpan = document.getElementById('produkCount');
    let cartCountSpan = document.getElementById('cartCount');

    // 🔥 DATA PRODUK DARI BACKEND
    let produkData = {};
    let produkById = {};
    let produkListData = [];
    @foreach ($produks as $produk)
        produkData["{{ $produk->barcode }}"] = {
            id: "{{ $produk->produk_id }}",
            nama: "{{ $produk->nama_produk }}",
            harga: {{ $produk->harga_jual }},
            satuan: "{{ $produk->satuan->nama_satuan ?? '-' }}",
            foto: "{{ $produk->photo ? asset('storage/' . $produk->photo) : 'https://via.placeholder.com/80' }}",
            stok: {{ $produk->stok ?? 0 }},
            barcode: "{{ $produk->barcode ?? '' }}",
            kategori_id: "{{ $produk->kategori_id ?? '' }}",
            kategori_nama: "{{ $produk->kategori->nama_kategori ?? 'Tanpa Kategori' }}"
        };
        produkById["{{ $produk->produk_id }}"] = {
            id: "{{ $produk->produk_id }}",
            nama: "{{ $produk->nama_produk }}",
            harga: {{ $produk->harga_jual }},
            satuan: "{{ $produk->satuan->nama_satuan ?? '-' }}",
            foto: "{{ $produk->photo ? asset('storage/' . $produk->photo) : 'https://via.placeholder.com/80' }}",
            stok: {{ $produk->stok ?? 0 }},
            barcode: "{{ $produk->barcode ?? '' }}",
            kategori_id: "{{ $produk->kategori_id ?? '' }}",
            kategori_nama: "{{ $produk->kategori->nama_kategori ?? 'Tanpa Kategori' }}"
        };
        produkListData.push({
            id: "{{ $produk->produk_id }}",
            nama: "{{ $produk->nama_produk }}",
            harga: {{ $produk->harga_jual }},
            satuan: "{{ $produk->satuan->nama_satuan ?? '-' }}",
            foto: "{{ $produk->photo ? asset('storage/' . $produk->photo) : 'https://via.placeholder.com/80' }}",
            stok: {{ $produk->stok ?? 0 }},
            barcode: "{{ $produk->barcode ?? '' }}",
            kategori_id: "{{ $produk->kategori_id ?? '' }}",
            kategori_nama: "{{ $produk->kategori->nama_kategori ?? 'Tanpa Kategori' }}"
        });
    @endforeach

    // 🔥 KONSTANTA QRIS
    const STATIC_QRIS =
        "00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214844519767362640303UMI51440014ID.CO.QRIS.WWW0215ID20243345184510303UMI5204541153033605802ID5920YANTO SHOP OK18846346005DEPOK61051641162070703A0163046879";

    // 🔥 FUNGSI UTAMA

    // Format Rupiah
    function formatRupiah(angka) {
        return 'Rp ' + (angka ? angka.toLocaleString('id-ID') : '0');
    }

    // Generate QR Code
    function generateQRCode(content) {
        const qr = qrcode(0, 'M');
        qr.addData(content);
        qr.make();
        return qr.createDataURL(8);
    }

    // Generate QRIS
    function generateQRIS(amount) {
        if (isNaN(amount) || amount <= 0) return '';

        let qris = STATIC_QRIS.slice(0, -4);
        let step1 = qris.replace("010211", "010212");
        let step2 = step1.split("5802ID");
        let uang = "54" + amount.toString().length.toString().padStart(2, '0') + amount.toString();
        uang += "5802ID";
        const fix = step2[0].trim() + uang + step2[1].trim();
        const finalQR = fix + ConvertCRC16(fix);

        return finalQR;
    }

    // Hitung CRC16
    function ConvertCRC16(str) {
        let crc = 0xFFFF;
        for (let c = 0; c < str.length; c++) {
            crc ^= str.charCodeAt(c) << 8;
            for (let i = 0; i < 8; i++) {
                crc = (crc & 0x8000) ? (crc << 1) ^ 0x1021 : crc << 1;
            }
        }
        let hex = (crc & 0xFFFF).toString(16).toUpperCase();
        return hex.length === 3 ? '0' + hex : hex.padStart(4, '0');
    }

    // 🔥 FUNGSI PENCARIAN DAN FILTER PRODUK

    // Filter dan tampilkan produk
    function filterAndDisplayProduk() {
        const searchTerm = searchProdukInput.value.toLowerCase();
        const kategoriId = filterKategoriSelect.value;

        let filteredProduk = produkListData.filter(produk => {
            const matchSearch = produk.nama.toLowerCase().includes(searchTerm) ||
                              produk.barcode.includes(searchTerm);
            const matchKategori = !kategoriId || produk.kategori_id == kategoriId;
            return matchSearch && matchKategori;
        });

        renderProdukGrid(filteredProduk);
    }

    // Render grid produk
    function renderProdukGrid(produkArray) {
        produkGrid.innerHTML = '';

        if (produkArray.length === 0) {
            produkEmptyState.style.display = 'block';
            produkCountSpan.textContent = '0 produk';
            return;
        }

        produkEmptyState.style.display = 'none';
        produkCountSpan.textContent = produkArray.length + ' produk';

        produkArray.forEach(produk => {
            const produkCard = document.createElement('div');
            produkCard.className = 'col-6 col-md-4 col-lg-3';
            produkCard.innerHTML = `
                <div class="produk-card" data-produk-id="${produk.id}">
                    <div class="d-flex align-items-start mb-2">
                        <img src="${produk.foto}" alt="${produk.nama}" class="me-2">
                        <div class="flex-grow-1">
                            <div class="produk-nama">${produk.nama}</div>
                            <div class="produk-harga">${formatRupiah(produk.harga)}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="produk-stok">Stok: ${produk.stok}</span>
                        <span class="produk-kategori">${produk.kategori_nama}</span>
                    </div>
                </div>
            `;

            produkCard.addEventListener('click', function() {
                addProduct(produk);
                const card = this.querySelector('.produk-card');
                card.classList.add('selected');
                setTimeout(() => card.classList.remove('selected'), 500);
            });

            produkGrid.appendChild(produkCard);
        });
    }

    // 🔥 FUNGSI TAMBAH PRODUK

    function addProduct(produk) {
        if (produk.stok < 1) {
            alert('⚠️ Stok produk habis!');
            return false;
        }

        let existingRow = null;
        produkList.querySelectorAll('tr').forEach(row => {
            let existingProdukId = row.querySelector('input[name="produk_id[]"]').value;
            if (existingProdukId == produk.id) {
                existingRow = row;
            }
        });

        if (existingRow) {
            let jumlahInput = existingRow.querySelector('.jumlah');
            let newJumlah = parseInt(jumlahInput.value) + 1;

            if (newJumlah > produk.stok) {
                alert('⚠️ Stok tidak mencukupi!\nStok tersisa: ' + produk.stok);
                return false;
            }

            jumlahInput.value = newJumlah;
            let subtotal = produk.harga * newJumlah;
            existingRow.querySelector('.subtotal').innerText = formatRupiah(subtotal);
            existingRow.style.backgroundColor = '#d4edda';
            setTimeout(() => existingRow.style.backgroundColor = '', 500);
        } else {
            let row = `
            <tr>
                <td><div class="d-flex justify-content-center">
                    <img src="${produk.foto}" alt="${produk.nama}" width="50" height="50" class="rounded object-fit-cover">
                </div></td>
                <td class="text-start"><div class="fw-medium">${produk.nama}</div>
                    <input type="hidden" name="produk_id[]" value="${produk.id}">
                </td>
                <td>${produk.satuan}</td>
                <td class="fw-semibold">${formatRupiah(produk.harga)}</td>
                <td><input type="number" name="jumlah_produk[]" value="1" min="1" max="${produk.stok}"
                       class="form-control form-control-sm jumlah"></td>
                <td class="subtotal fw-semibold">${formatRupiah(produk.harga)}</td>
                <td><button type="button" class="btn btn-outline-danger btn-sm btn-remove removeItem">
                    <i class="bi bi-trash"></i></button></td>
            </tr>`;
            produkList.insertAdjacentHTML('beforeend', row);
            
            let newRow = produkList.lastElementChild;
            newRow.style.backgroundColor = '#d4edda';
            setTimeout(() => newRow.style.backgroundColor = '', 500);
        }

        toggleEmptyState();
        hitungTotal();
        updateCartCount();
        return true;
    }

    // 🔥 BARCODE SCANNER - ULTRA AGGRESSIVE FOR PANDA PRJ-2200

    let barcodeTimeout = null;
    let isProcessing = false;
    let scanStartTime = 0;

    function addProductByBarcode(barcode) {
        if (isProcessing) {
            console.log('⏸️ Already processing');
            return;
        }
        
        barcode = barcode.trim();
        if (barcode.length === 0) {
            barcodeInput.value = '';
            barcodeInput.focus();
            return;
        }

        console.log('🔍 Processing:', barcode, 'Length:', barcode.length);

        let produk = produkData[barcode];
        if (!produk) {
            console.error('❌ Not found:', barcode);
            alert('⚠️ Produk dengan barcode ' + barcode + ' tidak ditemukan!');
            barcodeInput.value = '';
            barcodeInput.focus();
            return;
        }

        isProcessing = true;
        console.log('✅ Found:', produk.nama);
        
        if (addProduct(produk)) {
            barcodeInput.value = '';
            barcodeInput.focus();
        }
        
        setTimeout(() => isProcessing = false, 200);
    }

    // 🔥 METHOD 1: Input event (primary)
    barcodeInput.addEventListener('input', function(e) {
        const currentValue = this.value;
        const currentLength = currentValue.length;
        
        console.log('📝 Input:', currentValue, 'Len:', currentLength);
        clearTimeout(barcodeTimeout);
        
        if (currentLength === 1) {
            scanStartTime = Date.now();
        }
        
        const scanDuration = Date.now() - scanStartTime;
        console.log('⏱️ Duration:', scanDuration + 'ms');
        
        // EAN-13 exact length
        if (currentLength === 13) {
            console.log('📏 EAN-13 detected!');
            barcodeTimeout = setTimeout(() => {
                const barcode = this.value.trim();
                if (barcode.length === 13 && !isProcessing) {
                    console.log('🎯 Process EAN-13:', barcode);
                    addProductByBarcode(barcode);
                }
            }, 50);
        }
        // Flexible length 8-14
        else if (currentLength >= 8 && currentLength <= 14) {
            console.log('📊 Valid length');
            barcodeTimeout = setTimeout(() => {
                const barcode = this.value.trim();
                if (barcode.length >= 8 && !isProcessing) {
                    console.log('🟢 Process barcode:', barcode);
                    addProductByBarcode(barcode);
                }
            }, 150);
        }
        // Fast typing
        else if (currentLength > 3 && scanDuration < 200) {
            console.log('⚡ Fast input!');
            barcodeTimeout = setTimeout(() => {
                const barcode = this.value.trim();
                if (barcode.length >= 8 && !isProcessing) {
                    console.log('🔥 Process fast:', barcode);
                    addProductByBarcode(barcode);
                }
            }, 100);
        }
    });

    // 🔥 METHOD 2: Enter key (fallback)
    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            console.log('⏎ ENTER pressed');
            clearTimeout(barcodeTimeout);
            
            const barcode = this.value.trim();
            if (barcode.length > 0) {
                console.log('🔵 Process via ENTER:', barcode);
                addProductByBarcode(barcode);
            }
            return false;
        }
        
        // Debug: log all keys
        console.log('🔑 KEY:', e.key, 'Code:', e.code);
    });

    // 🔥 METHOD 3: Blur (extra safety)
    barcodeInput.addEventListener('blur', function() {
        const barcode = this.value.trim();
        if (barcode.length >= 8 && !isProcessing) {
            console.log('👁️ Blur with:', barcode);
            setTimeout(() => {
                if (!isProcessing) {
                    console.log('🟡 Process on blur:', barcode);
                    addProductByBarcode(barcode);
                }
            }, 100);
        }
    });

    // 🔥 FUNGSI LAINNYA

    function togglePaymentInputs() {
        const metode = metodeSelect.value;
        if (metode === 'cash') {
            cashSection.style.display = 'block';
            kembalianSection.style.display = 'block';
            qrisSection.style.display = 'none';
            jumlahBayarInput.required = true;
        } else if (metode === 'qris') {
            cashSection.style.display = 'none';
            kembalianSection.style.display = 'none';
            qrisSection.style.display = 'block';
            jumlahBayarInput.required = false;
            const totalSetelahDiskon = parseInt(totalHargaSpan.innerText.replace(/\D/g, '')) || 0;
            jumlahBayarInput.value = totalSetelahDiskon;
            generateQRISBarcode(totalSetelahDiskon);
        } else {
            cashSection.style.display = 'block';
            kembalianSection.style.display = 'block';
            qrisSection.style.display = 'none';
        }
    }

    function generateQRISBarcode(amount) {
        if (amount <= 0) {
            qrisBarcodeContainer.innerHTML = '<p class="text-muted">Total harus lebih dari 0</p>';
            qrisAmountDisplay.textContent = 'Rp 0';
            return;
        }
        const qrContent = generateQRIS(amount);
        const qrImageUrl = generateQRCode(qrContent);
        qrisBarcodeContainer.innerHTML = `<img src="${qrImageUrl}" alt="QR Code" class="img-fluid">`;
        qrisAmountDisplay.textContent = formatRupiah(amount);
    }

    function isPelangganTerdaftar() {
        return pelangganSelect.value !== '';
    }

    function updateDiskonInfo() {
        diskonInfoBox.style.display = isPelangganTerdaftar() ? 'block' : 'none';
    }

    function toggleEmptyState() {
        emptyState.style.display = produkList.children.length === 0 ? 'block' : 'none';
    }

    function updateCartCount() {
        cartCountSpan.textContent = produkList.children.length + ' item';
    }

    function hitungTotal() {
        let subtotal = 0;
        produkList.querySelectorAll('tr').forEach(row => {
            subtotal += parseInt(row.querySelector('.subtotal').innerText.replace(/\D/g, '')) || 0;
        });

        let nominalDiskon = 0;
        if (isPelangganTerdaftar() && subtotal >= 100000) {
            nominalDiskon = Math.floor(subtotal * 0.05);
            diskonSection.style.display = 'flex';
            diskonNote.style.display = 'block';
            diskonHemat.innerText = formatRupiah(nominalDiskon);
        } else {
            diskonSection.style.display = 'none';
            diskonNote.style.display = 'none';
        }

        subtotalHargaSpan.innerText = formatRupiah(subtotal);
        nominalDiskonSpan.innerText = '- ' + formatRupiah(nominalDiskon);
        let totalSetelahDiskon = subtotal - nominalDiskon;
        totalHargaSpan.innerText = formatRupiah(totalSetelahDiskon);

        if (metodeSelect.value === 'qris') {
            jumlahBayarInput.value = totalSetelahDiskon;
            generateQRISBarcode(totalSetelahDiskon);
        }
        updateKembalian(totalSetelahDiskon);
    }

    function updateKembalian(totalSetelahDiskon) {
        let bayar = parseInt(jumlahBayarInput.value) || 0;
        let kembali = bayar - totalSetelahDiskon;

        if (kembali < 0) {
            kembalianInput.value = 'Kurang: ' + formatRupiah(Math.abs(kembali));
            kembalianInput.classList.add('text-danger', 'fw-bold');
            kembalianInput.classList.remove('text-primary');
        } else {
            kembalianInput.value = formatRupiah(kembali);
            kembalianInput.classList.add('text-primary', 'fw-bold');
            kembalianInput.classList.remove('text-danger');
        }
    }

    // 🔥 EVENT LISTENERS

    clearBarcodeBtn.addEventListener('click', function() {
        barcodeInput.value = '';
        clearTimeout(barcodeTimeout);
        barcodeInput.focus();
    });

    searchProdukInput.addEventListener('input', filterAndDisplayProduk);
    filterKategoriSelect.addEventListener('change', filterAndDisplayProduk);
    metodeSelect.addEventListener('change', togglePaymentInputs);
    
    pelangganSelect.addEventListener('change', function() {
        updateDiskonInfo();
        hitungTotal();
    });

    jumlahBayarInput.addEventListener('input', function() {
        let totalSetelahDiskon = parseInt(totalHargaSpan.innerText.replace(/\D/g, '')) || 0;
        updateKembalian(totalSetelahDiskon);
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        let totalSetelahDiskon = parseInt(totalHargaSpan.innerText.replace(/\D/g, '')) || 0;
        let jumlahBayar = parseInt(jumlahBayarInput.value) || 0;
        const metode = metodeSelect.value;

        if (produkList.children.length === 0) {
            e.preventDefault();
            alert('⚠️ Belum ada produk yang dipilih!');
            return;
        }

        if (metode === 'cash' && jumlahBayar < totalSetelahDiskon) {
            e.preventDefault();
            let kurang = totalSetelahDiskon - jumlahBayar;
            alert('⚠️ Jumlah bayar kurang dari total!\nKurang: Rp ' + kurang.toLocaleString('id-ID'));
            jumlahBayarInput.focus();
            return;
        }

        if (metode === 'qris' && jumlahBayar !== totalSetelahDiskon) {
            e.preventDefault();
            alert('⚠️ Untuk pembayaran QRIS, jumlah bayar harus sama dengan total!');
            return;
        }
    });

    produkList.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeItem') || e.target.closest('.removeItem')) {
            let button = e.target.classList.contains('removeItem') ? e.target : e.target.closest('.removeItem');
            button.closest('tr').remove();
            toggleEmptyState();
            hitungTotal();
            updateCartCount();
        }
    });

    produkList.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlah')) {
            let row = e.target.closest('tr');
            let hargaText = row.querySelector('td:nth-child(4)').innerText;
            let harga = parseInt(hargaText.replace(/\D/g, '')) || 0;
            let jumlah = parseInt(e.target.value) || 0;

            let produkId = row.querySelector('input[name="produk_id[]"]').value;
            let produk = produkById[produkId];

            if (produk) {
                if (jumlah > produk.stok) {
                    alert('⚠️ Stok tidak mencukupi!\nStok tersisa: ' + produk.stok);
                    e.target.value = produk.stok;
                    e.target.classList.add('is-invalid');
                    jumlah = produk.stok;
                } else {
                    e.target.classList.remove('is-invalid');
                }
            }

            row.querySelector('.subtotal').innerText = formatRupiah(harga * jumlah);
            hitungTotal();
        }
    });

    // Auto-focus barcode
    window.addEventListener('load', () => barcodeInput.focus());
    
    document.addEventListener('click', function(e) {
        if (!e.target.matches('input, select, button, a, .produk-card')) {
            barcodeInput.focus();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (document.activeElement.tagName === 'INPUT' || 
            document.activeElement.tagName === 'SELECT' ||
            document.activeElement.tagName === 'TEXTAREA') return;
        if (e.ctrlKey || e.altKey || e.metaKey || e.key === 'Tab') return;
        barcodeInput.focus();
    });

    // 🔥 MODAL HANDLING
    @if (session('success') && session('penjualan_id'))
        document.addEventListener('DOMContentLoaded', function() {
            let penjualanId = {{ session('penjualan_id') }};
            let metodePembayaran = '{{ session('metode_pembayaran', 'cash') }}';
            let totalBayar = {{ session('total_bayar', 0) }};

            if (metodePembayaran === 'qris') {
                showQRISModal(totalBayar, penjualanId);
            } else {
                showSuccessModal(penjualanId);
            }
        });
    @endif

    function showQRISModal(amount, penjualanId) {
        const qrContent = generateQRIS(amount);
        const qrImageUrl = generateQRCode(qrContent);
        document.getElementById('qrisAmount').textContent = formatRupiah(amount);
        document.getElementById('qrCodeContainer').innerHTML = `
            <img src="${qrImageUrl}" alt="QR Code" style="max-width: 250px; height: auto;">
        `;
        const qrisModal = new bootstrap.Modal(document.getElementById('qrisModal'));
        qrisModal.show();
        document.getElementById('confirmQRIS').addEventListener('click', function() {
            qrisModal.hide();
            setTimeout(() => showSuccessModal(penjualanId), 300);
        });
    }

    function showSuccessModal(penjualanId) {
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
        document.getElementById('cetakStrukBtn').addEventListener('click', function() {
            window.open('{{ route('penjualan.struk', '') }}/' + penjualanId, '_blank');
            successModal.hide();
            setTimeout(() => resetForm(), 300);
        });
        document.getElementById('okeSajaBtn').addEventListener('click', function() {
            successModal.hide();
            setTimeout(() => resetForm(), 300);
        });
    }

    function resetForm() {
        pelangganSelect.value = '';
        metodeSelect.value = '';
        jumlahBayarInput.value = '';
        kembalianInput.value = '';
        produkList.innerHTML = '';
        barcodeInput.value = '';
        searchProdukInput.value = '';
        filterKategoriSelect.value = '';
        clearTimeout(barcodeTimeout);
        updateDiskonInfo();
        toggleEmptyState();
        hitungTotal();
        togglePaymentInputs();
        filterAndDisplayProduk();
        updateCartCount();
        barcodeInput.focus();
    }

    // 🔥 INISIALISASI
    document.addEventListener('DOMContentLoaded', function() {
        updateDiskonInfo();
        toggleEmptyState();
        hitungTotal();
        togglePaymentInputs();
        filterAndDisplayProduk();
        updateCartCount();
        barcodeInput.focus();
        
        console.log('✅ Scanner Ready - Panda PRJ-2200');
        console.log('📊 Products loaded:', Object.keys(produkData).length);
    });
</script>
@endsection