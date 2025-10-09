@extends('layout.master')

@section('title', 'Tambah Pembelian')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-shopping-cart me-2"></i> Tambah Pembelian
            </h3>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Card Form --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('pembelian.store') }}" method="POST">
                    @csrf

                    {{-- Input tanggal --}}
                    <div class="mb-3">
                        <label for="tanggalInput" class="form-label">Tanggal Pembelian</label>
                        <input type="date" name="tanggal" id="tanggalInput" class="form-control" required>
                    </div>

                    {{-- Input supplier --}}
                    <div class="mb-4">
                        <label for="supplier" class="form-label">Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->supplier_id }}">{{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Detail Produk --}}
                    <h5 class="mb-3 text-primary"><i class="fas fa-boxes me-1"></i> Detail Produk</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="produkTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Harga Beli</th>
                                    <th>Subtotal</th>
                                    <th width="5%">
                                        <button type="button" class="btn btn-success btn-sm" id="addRow">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="produk_id[]" class="form-select produkSelect" required>
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($produks as $p)
                                                <option value="{{ $p->produk_id }}" data-harga="{{ $p->harga_beli }}"
                                                    data-kategori="{{ $p->kategori->nama_kategori ?? '' }}"
                                                    data-satuan="{{ $p->satuan->nama_satuan ?? '' }}">
                                                    {{ $p->nama_produk }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control kategoriNama" readonly></td>
                                    <td><input type="text" class="form-control satuanNama" readonly></td>
                                    <td><input type="number" name="jumlah[]" class="form-control jumlah text-end"
                                            value="1" min="1" required></td>
                                    <td><input type="number" step="0.01" name="harga_beli[]"
                                            class="form-control hargaBeli text-end" value="0" readonly></td>
                                    <td><input type="text" class="form-control subtotal text-end" readonly></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm removeRow">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Total --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <h5 class="mb-0 text-success">
                            Total: Rp <span id="totalHarga">0</span>
                        </h5>
                    </div>

                    {{-- Tombol --}}
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Set tanggal hari ini otomatis
            const today = new Date();
            const formattedDate = today.getFullYear() + '-' +
                String(today.getMonth() + 1).padStart(2, '0') + '-' +
                String(today.getDate()).padStart(2, '0');
            document.getElementById('tanggalInput').value = formattedDate;

            function hitungSubtotal(row) {
                let jumlah = parseFloat(row.querySelector(".jumlah").value) || 0;
                let harga = parseFloat(row.querySelector(".hargaBeli").value) || 0;
                let subtotal = jumlah * harga;
                row.querySelector(".subtotal").value = subtotal.toLocaleString('id-ID');
                return subtotal;
            }

            function hitungTotal() {
                let total = 0;
                document.querySelectorAll("#produkTable tbody tr").forEach(row => {
                    total += hitungSubtotal(row);
                });
                document.getElementById("totalHarga").textContent = total.toLocaleString('id-ID');
            }

            // Ganti produk
            document.addEventListener("change", function(e) {
                if (e.target.classList.contains("produkSelect")) {
                    let selectedOption = e.target.options[e.target.selectedIndex];
                    let row = e.target.closest("tr");

                    if (selectedOption.value) {
                        row.querySelector(".hargaBeli").value = selectedOption.getAttribute("data-harga") ||
                            0;
                        row.querySelector(".kategoriNama").value = selectedOption.getAttribute(
                            "data-kategori") || '';
                        row.querySelector(".satuanNama").value = selectedOption.getAttribute(
                            "data-satuan") || '';
                    } else {
                        row.querySelector(".hargaBeli").value = 0;
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

            // Tambah baris
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

            // Hapus baris
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
@endsection
