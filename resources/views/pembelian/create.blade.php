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
                    <div class="col-md-4">
                        <label for="tanggalInput" class="form-label fw-semibold">
                            <i class="fas fa-calendar-day me-1 text-primary"></i> Tanggal Pembelian
                        </label>
                        <input type="datetime-local" name="tanggal" id="tanggalInput" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-primary"></i> Admin
                        </label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->name ?? 'Admin' }}" readonly>
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    </div>
                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label fw-semibold">
                            <i class="fas fa-truck me-1 text-primary"></i> Supplier
                        </label>
                        <select name="supplier_id" id="supplier_id" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->supplier_id }}">{{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
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
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Harga Beli</th>
                                    <th>Subtotal</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-row">
                                    <td>
                                        <select name="produk_id[]" class="form-select produkSelect" required>
                                            <option>-- Pilih Produk --</option>
                                            @foreach ($produks as $p)
                                                <option value="{{ $p->produk_id }}" 
                                                    data-harga="{{ $p->harga_beli }}"
                                                    data-kategori="{{ $p->kategori->nama_kategori ?? '' }}"
                                                    data-satuan="{{ $p->satuan->nama_satuan ?? '' }}">
                                                    {{ $p->nama_produk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control kategoriNama bg-light" readonly></td>
                                    <td><input type="text" class="form-control satuanNama bg-light" readonly></td>
                                    <td><input type="number" name="jumlah[]" class="form-control jumlah text-end" value="1" min="1" required></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" name="harga_beli[]" class="form-control hargaBeli text-end bg-light" value="0" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control subtotal text-end bg-light" readonly>
                                        </div>
                                    </td>
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
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save me-2"></i> Simpan Pembelian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Format angka ke format Indonesia dengan titik sebagai pemisah ribuan
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Parse format number kembali ke angka
        function parseFormattedNumber(str) {
            return parseFloat(str.replace(/\./g, ''));
        }

        // Set tanggal dan waktu sekarang otomatis
        function setCurrentDateTime() {
            const now = new Date();
            
            // Format untuk datetime-local input (YYYY-MM-DDTHH:MM)
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const datetimeLocal = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('tanggalInput').value = datetimeLocal;
            
            // Format untuk display di header
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

        // Panggil fungsi untuk set tanggal dan waktu
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

        // Ganti produk
        document.addEventListener("change", function(e) {
            if (e.target.classList.contains("produkSelect")) {
                let selectedOption = e.target.options[e.target.selectedIndex];
                let row = e.target.closest("tr");

                if (selectedOption.value) {
                    let harga = selectedOption.getAttribute("data-harga") || 0;
                    row.querySelector(".hargaBeli").value = formatNumber(harga);
                    row.querySelector(".kategoriNama").value = selectedOption.getAttribute(
                        "data-kategori") || '';
                    row.querySelector(".satuanNama").value = selectedOption.getAttribute(
                        "data-satuan") || '';
                } else {
                    row.querySelector(".hargaBeli").value = '0';
                    row.querySelector(".kategoriNama").value = '';
                    row.querySelector(".satuanNama").value = '';
                }

                hitungTotal();
            }
        });

        // Perubahan jumlah
        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("jumlah")) {
                hitungTotal();
            }
        });

        // Tambah baris produk
        document.getElementById("addRow").addEventListener("click", function() {
            let row = document.querySelector("#produkTable tbody tr").cloneNode(true);

            row.querySelectorAll("input").forEach(input => {
                if (input.classList.contains("jumlah")) {
                    input.value = "1";
                } else if (input.classList.contains("hargaBeli") || input.classList.contains("subtotal")) {
                    input.value = "0";
                } else {
                    input.value = "";
                }
            });
            row.querySelectorAll("select").forEach(select => select.selectedIndex = 0);
            document.querySelector("#produkTable tbody").appendChild(row);
            hitungTotal();
        });

        // Hapus baris produk
        document.addEventListener("click", function(e) {
            if (e.target.closest(".removeRow")) {
                if (document.querySelectorAll("#produkTable tbody tr").length > 1) {
                    e.target.closest("tr").remove();
                    hitungTotal();
                }
            }
        });

        hitungTotal();
    });
</script>

{{-- Style --}}
<style>
    /* Seragamkan semua input dan select */
    .form-control, .form-select {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.45rem 0.75rem;
        height: 42px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    #produkTable td {
        vertical-align: middle;
        padding: 0.5rem;
    }

    .input-group-text {
        background-color: #f1f3f5;
        border-radius: 0.5rem 0 0 0.5rem;
    }

    .table th {
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
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
</style>
@endsection