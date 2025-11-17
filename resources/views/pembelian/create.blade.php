@extends('layout.master')

@section('title', 'Tambah Pembelian')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-shopping-cart me-2"></i> Tambah Pembelian
            </h3>
            <div class="text-muted">
                <i class="fas fa-calendar-alt me-1"></i>
                <span id="currentDateTime"></span>
            </div>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                    <div>
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Modal Barcode Scanner --}}
        <div class="modal fade" id="barcodeScannerModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-camera me-2"></i> Scan Barcode dengan Kamera
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="reader" style="width: 100%;"></div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Arahkan kamera ke barcode produk
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Form --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i> Form Pembelian Baru
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pembelian.store') }}" method="POST" id="formPembelian">
                    @csrf

                    {{-- Informasi Utama --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="tanggalInput" class="form-label fw-semibold">
                                <i class="fas fa-calendar-day me-1 text-primary"></i> Tanggal Pembelian
                            </label>
                            <input type="datetime-local" name="tanggal" id="tanggalInput" class="form-control" required>
                            <small class="text-muted">Format: Tanggal dan Waktu</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1 text-primary"></i> Admin
                            </label>
                            <input type="text" class="form-control bg-light"
                                value="{{ auth()->user()->name ?? 'Admin' }}" readonly>
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                        </div>
                    </div>

                    {{-- Detail Produk --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-primary mb-0">
                                <i class="fas fa-boxes me-2"></i> Detail Produk
                            </h5>
                            <button type="button" class="btn btn-primary" id="addRow">
                                <i class="fas fa-plus me-1"></i> Tambah Baris
                            </button>
                        </div>

                        <div class="table-responsive rounded">
                            <table class="table table-bordered align-middle" id="produkTable">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th width="15%">Produk</th>
                                        <th width="12%">Barcode</th>
                                        <th width="10%">Supplier</th>
                                        <th width="8%">Kategori</th>
                                        <th width="8%">Satuan</th>
                                        <th width="8%">Jumlah</th>
                                        <th width="10%">Harga Beli</th>
                                        <th width="10%">Kadaluwarsa</th>
                                        <th width="10%">Subtotal</th>
                                        <th width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-row">
                                        <td>
                                            <select name="produk_id[]" class="form-select produkSelect" required>
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach ($produks as $p)
                                                    <option value="{{ $p->produk_id }}"
                                                        data-harga="{{ $p->harga_beli ?? 0 }}"
                                                        data-kategori="{{ $p->kategori->nama_kategori ?? '-' }}"
                                                        data-satuan="{{ $p->satuan->nama_satuan ?? '-' }}"
                                                        data-supplier-id="{{ $p->supplier_id ?? '' }}"
                                                        data-supplier-nama="{{ $p->supplier->nama_supplier ?? '-' }}">
                                                        {{ $p->nama_produk }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" name="barcode[]" class="form-control barcodeInput"
                                                    placeholder="Scan/Input barcode baru" required>
                                                <button type="button" class="btn btn-outline-primary scanBarcodeBtn"
                                                    title="Scan dengan Kamera">
                                                    <i class="fas fa-camera"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">⚡ Scan atau ketik barcode BARU</small>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control supplierNama bg-light text-center"
                                                readonly placeholder="-">
                                            <input type="hidden" name="supplier_id[]" class="supplierId">
                                        </td>
                                        <td><input type="text" class="form-control kategoriNama bg-light text-center"
                                                readonly placeholder="-"></td>
                                        <td><input type="text" class="form-control satuanNama bg-light text-center"
                                                readonly placeholder="-"></td>
                                        <td><input type="number" name="jumlah[]" class="form-control jumlah text-end"
                                                value="1" min="1" required></td>
                                        <td><input type="text" name="harga_beli[]"
                                                class="form-control hargaBeli text-end bg-light" value="0" readonly></td>
                                        <td>
                                            <input type="date" name="kadaluwarsa[]" class="form-control kadaluwarsaInput"
                                                placeholder="Pilih tanggal" required>
                                        </td>
                                        <td><input type="text" class="form-control subtotal text-end bg-light" readonly
                                                placeholder="0"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm removeRow">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="card bg-light mb-4 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-dark fw-semibold">Total Pembelian:</h5>
                                <h4 class="mb-0 text-success fw-bold">
                                    Rp <span id="totalHarga">0</span>
                                </h4>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-success px-4" id="btnSubmit">
                            <i class="fas fa-save me-2"></i> Simpan Pembelian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Load html5-qrcode Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

    {{-- Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let currentScanRow = null;
            let html5QrcodeScanner = null;

            // === Format angka ===
            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function parseFormattedNumber(str) {
                if (!str) return 0;
                return parseFloat(str.toString().replace(/\./g, '').replace(/,/g, ''));
            }

            // === Set tanggal & waktu otomatis ===
            function setCurrentDateTime() {
                const now = new Date();
                const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
                const datetimeLocal = local.toISOString().slice(0, 16);
                document.getElementById('tanggalInput').value = datetimeLocal;

                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                document.getElementById('currentDateTime').textContent = now.toLocaleDateString('id-ID', options);
            }

            setCurrentDateTime();
            setInterval(setCurrentDateTime, 1000);

            // === Hitung subtotal & total ===
            function hitungSubtotal(row) {
                let jumlah = parseFloat(row.querySelector(".jumlah").value) || 0;
                let harga = parseFormattedNumber(row.querySelector(".hargaBeli").value) || 0;
                let subtotal = jumlah * harga;
                row.querySelector(".subtotal").value = formatNumber(subtotal);
                return subtotal;
            }

            function hitungTotal() {
                let total = 0;
                document.querySelectorAll("#produkTable tbody tr").forEach(row => total += hitungSubtotal(row));
                document.getElementById("totalHarga").textContent = formatNumber(total);
            }

            // === Event produk dipilih ===
            document.addEventListener("change", function(e) {
                if (e.target.classList.contains("produkSelect")) {
                    let selected = e.target.selectedOptions[0];
                    let row = e.target.closest("tr");

                    if (selected.value) {
                        // ✅ Auto-fill data produk (kategori, satuan, harga, supplier)
                        row.querySelector(".hargaBeli").value = formatNumber(selected.dataset.harga || 0);
                        row.querySelector(".kategoriNama").value = selected.dataset.kategori || '-';
                        row.querySelector(".satuanNama").value = selected.dataset.satuan || '-';
                        row.querySelector(".supplierNama").value = selected.dataset.supplierNama || '-';
                        row.querySelector(".supplierId").value = selected.dataset.supplierId || '';

                        // ✅ JANGAN auto-fill barcode & kadaluwarsa! User harus input sendiri
                        row.querySelector(".barcodeInput").value = '';
                        row.querySelector(".kadaluwarsaInput").value = '';
                        
                        // Focus ke input barcode untuk langsung scan/ketik
                        setTimeout(() => {
                            row.querySelector(".barcodeInput").focus();
                        }, 100);
                    } else {
                        // Reset semua field kecuali jumlah
                        row.querySelectorAll("input").forEach(input => {
                            if (!input.classList.contains("jumlah")) {
                                input.value = '';
                            }
                        });
                    }
                    hitungTotal();
                }
            });

            // === Event jumlah berubah ===
            document.addEventListener("input", e => {
                if (e.target.classList.contains("jumlah")) hitungTotal();
            });

            // === Tambah baris produk ===
            document.getElementById("addRow").addEventListener("click", function() {
                let row = document.querySelector("#produkTable tbody tr").cloneNode(true);
                row.querySelectorAll("input").forEach(input => {
                    if (input.classList.contains("jumlah")) {
                        input.value = "1";
                    } else {
                        input.value = "";
                    }
                });
                row.querySelectorAll("select").forEach(select => select.selectedIndex = 0);
                document.querySelector("#produkTable tbody").appendChild(row);
                hitungTotal();
            });

            // === Hapus baris ===
            document.addEventListener("click", function(e) {
                if (e.target.closest(".removeRow")) {
                    if (document.querySelectorAll("#produkTable tbody tr").length > 1) {
                        e.target.closest("tr").remove();
                        hitungTotal();
                    } else {
                        alert('Minimal harus ada satu baris produk!');
                    }
                }
            });

            // === BARCODE SCANNER dengan Kamera ===
            document.addEventListener("click", function(e) {
                if (e.target.closest(".scanBarcodeBtn")) {
                    currentScanRow = e.target.closest("tr");
                    const modal = new bootstrap.Modal(document.getElementById('barcodeScannerModal'));
                    modal.show();

                    setTimeout(() => {
                        startBarcodeScanner();
                    }, 500);
                }
            });

            function startBarcodeScanner() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                }

                html5QrcodeScanner = new Html5Qrcode("reader");

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText, decodedResult) => {
                        if (currentScanRow) {
                            currentScanRow.querySelector(".barcodeInput").value = decodedText;
                        }

                        html5QrcodeScanner.stop().then(() => {
                            bootstrap.Modal.getInstance(document.getElementById('barcodeScannerModal')).hide();
                            playBeep();
                        });
                    },
                    (errorMessage) => {}
                ).catch((err) => {
                    alert("Gagal mengakses kamera: " + err);
                });
            }

            document.getElementById('barcodeScannerModal').addEventListener('hidden.bs.modal', function() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().catch(err => console.log(err));
                }
            });

            function playBeep() {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.3;

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            }

            // === Support Hardware Barcode Scanner ===
            let barcodeBuffer = '';
            let barcodeTimeout = null;

            document.addEventListener("keypress", function(e) {
                if (e.target.classList.contains("barcodeInput")) {
                    clearTimeout(barcodeTimeout);

                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (barcodeBuffer.length > 0) {
                            e.target.value = barcodeBuffer;
                            playBeep();
                            barcodeBuffer = '';
                        }
                    } else {
                        barcodeBuffer += e.key;
                        barcodeTimeout = setTimeout(() => {
                            barcodeBuffer = '';
                        }, 100);
                    }
                }
            });

            hitungTotal();
        });
    </script>

    {{-- Style --}}
    <style>
        .form-control,
        .form-select {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.45rem 0.75rem;
            height: 42px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        #produkTable td {
            vertical-align: middle;
            padding: 0.5rem;
        }

        .table th {
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .btn {
            border-radius: 0.4rem;
        }

        .card {
            border-radius: 0.75rem;
        }

        .input-group .btn {
            height: 42px;
        }

        #reader {
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            padding: 10px;
        }

        .barcodeInput {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
    </style>
@endsection