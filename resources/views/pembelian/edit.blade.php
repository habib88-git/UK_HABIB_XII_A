@extends('layout.master')

@section('title', 'Edit Pembelian')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-edit me-2"></i> Edit Pembelian
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

        {{-- Alert Warning --}}
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Perhatian!</strong>
            <p class="mb-0">Edit pembelian akan menghapus batch lama dan membuat batch baru. Pastikan data sudah benar!</p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        {{-- Card Form --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i> Form Edit Pembelian
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pembelian.update', $pembelian->pembelian_id) }}" method="POST" id="formPembelian">
                    @csrf
                    @method('PUT')

                    {{-- Informasi Utama --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="tanggalInput" class="form-label fw-semibold">
                                <i class="fas fa-calendar-day me-1 text-primary"></i> Tanggal Pembelian
                            </label>
                            @php
                                $tanggalWithTime = \Carbon\Carbon::parse($pembelian->tanggal);
                                if ($tanggalWithTime->format('H:i:s') === '00:00:00') {
                                    $tanggalWithTime = now();
                                }
                            @endphp
                            <input type="datetime-local" name="tanggal" id="tanggalInput" class="form-control"
                                value="{{ old('tanggal', $tanggalWithTime->format('Y-m-d\TH:i')) }}" required>
                            <small class="text-muted">Format: Tanggal dan Waktu</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1 text-primary"></i> Admin
                            </label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $pembelian->user->name ?? 'Admin' }}" readonly>
                            <input type="hidden" name="user_id" value="{{ $pembelian->user_id }}">
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
                                        <th width="12%">Produk</th>
                                        <th width="10%">Barcode Batch</th>
                                        <th width="8%">Supplier</th>
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
                                    @foreach ($pembelian->details as $index => $detail)
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
                                                            data-supplier-nama="{{ $p->supplier->nama_supplier ?? '-' }}"
                                                            {{ old('produk_id.' . $index, $detail->produk_id) == $p->produk_id ? 'selected' : '' }}>
                                                            {{ $p->nama_produk }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="barcode[]" class="form-control barcodeInput text-center"
                                                    value="{{ old('barcode.' . $index, $detail->barcode_batch ?? '') }}" 
                                                    placeholder="Input barcode" required>
                                                <small class="text-muted">Batch baru</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control supplierNama bg-light text-center"
                                                    readonly value="{{ $detail->produk->supplier->nama_supplier ?? '-' }}">
                                                <input type="hidden" name="supplier_id[]" class="supplierId"
                                                    value="{{ $detail->produk->supplier_id ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control kategoriNama bg-light text-center"
                                                    readonly value="{{ $detail->produk->kategori->nama_kategori ?? '-' }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control satuanNama bg-light text-center"
                                                    readonly value="{{ $detail->produk->satuan->nama_satuan ?? '-' }}">
                                            </td>
                                            <td>
                                                <input type="number" name="jumlah[]" class="form-control jumlah text-end"
                                                    value="{{ old('jumlah.' . $index, $detail->jumlah) }}" min="1" required>
                                            </td>
                                            <td>
                                                <input type="text" name="harga_beli[]"
                                                    class="form-control hargaBeli text-end bg-light"
                                                    value="{{ number_format(old('harga_beli.' . $index, $detail->harga_beli), 0, ',', '.') }}"
                                                    readonly>
                                            </td>
                                            <td>
                                                <input type="date" name="kadaluwarsa[]" class="form-control kadaluwarsaInput"
                                                    value="{{ old('kadaluwarsa.' . $index, $detail->kadaluwarsa ? \Carbon\Carbon::parse($detail->kadaluwarsa)->format('Y-m-d') : '') }}" 
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control subtotal text-end bg-light"
                                                    readonly value="{{ number_format($detail->subtotal, 0, ',', '.') }}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm removeRow">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
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
                                    Rp <span id="totalHarga">{{ number_format($pembelian->total_harga, 0, ',', '.') }}</span>
                                </h4>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning px-4" id="btnSubmit">
                            <i class="fas fa-save me-2"></i> Update Pembelian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            function parseFormattedNumber(str) {
                if (!str) return 0;
                return parseFloat(str.toString().replace(/\./g, '').replace(/,/g, ''));
            }

            function setCurrentDateTime() {
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                document.getElementById('currentDateTime').textContent = new Date().toLocaleDateString('id-ID', options);
            }

            setCurrentDateTime();

            function hitungSubtotal(row) {
                let jumlah = parseFloat(row.querySelector(".jumlah").value) || 0;
                let hargaInput = row.querySelector(".hargaBeli");
                let harga = parseFormattedNumber(hargaInput.value) || 0;
                let subtotal = jumlah * harga;

                row.querySelector(".subtotal").value = formatNumber(subtotal);
                return subtotal;
            }

            function hitungTotal() {
                let total = 0;
                document.querySelectorAll("#produkTable tbody tr").forEach(row => {
                    total += hitungSubtotal(row);
                });
                document.getElementById("totalHarga").textContent = formatNumber(total);
            }

            document.addEventListener("change", function(e) {
                if (e.target.classList.contains("produkSelect")) {
                    let selectedOption = e.target.options[e.target.selectedIndex];
                    let row = e.target.closest("tr");

                    if (selectedOption.value) {
                        let harga = selectedOption.getAttribute("data-harga") || 0;
                        let kategori = selectedOption.getAttribute("data-kategori") || '-';
                        let satuan = selectedOption.getAttribute("data-satuan") || '-';
                        let supplierId = selectedOption.getAttribute("data-supplier-id") || '';
                        let supplierNama = selectedOption.getAttribute("data-supplier-nama") || '-';

                        row.querySelector(".hargaBeli").value = formatNumber(harga);
                        row.querySelector(".kategoriNama").value = kategori;
                        row.querySelector(".satuanNama").value = satuan;
                        row.querySelector(".supplierNama").value = supplierNama;
                        row.querySelector(".supplierId").value = supplierId;

                        // Clear barcode & kadaluwarsa untuk input baru
                        row.querySelector(".barcodeInput").value = '';
                        row.querySelector(".kadaluwarsaInput").value = '';
                        
                        // Focus ke barcode
                        setTimeout(() => {
                            row.querySelector(".barcodeInput").focus();
                        }, 100);
                    } else {
                        row.querySelector(".hargaBeli").value = '0';
                        row.querySelector(".kategoriNama").value = '';
                        row.querySelector(".satuanNama").value = '';
                        row.querySelector(".supplierNama").value = '';
                        row.querySelector(".supplierId").value = '';
                    }

                    hitungTotal();
                }
            });

            document.addEventListener("input", function(e) {
                if (e.target.classList.contains("jumlah")) {
                    hitungTotal();
                }
            });

            document.getElementById("addRow").addEventListener("click", function() {
                let row = document.querySelector("#produkTable tbody tr").cloneNode(true);

                row.querySelectorAll("input").forEach(input => {
                    if (input.classList.contains("jumlah")) {
                        input.value = "1";
                    } else if (input.classList.contains("hargaBeli")) {
                        input.value = "0";
                    } else if (input.classList.contains("subtotal")) {
                        input.value = "0";
                    } else if (!input.classList.contains("supplierId")) {
                        input.value = "";
                    }
                });

                row.querySelectorAll("select").forEach(select => {
                    select.selectedIndex = 0;
                });

                document.querySelector("#produkTable tbody").appendChild(row);
                hitungTotal();
            });

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

        .barcodeInput {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
    </style>
@endsection