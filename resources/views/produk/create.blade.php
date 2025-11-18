@extends('layout.master')
@section('title', 'Tambah Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box"></i> Tambah Produk
            </h1>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Alert Info --}}
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-info-circle"></i> Informasi Sistem Batch!</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Barcode:</strong> Barcode master sebagai identitas produk utama</li>
                <li><strong>Stok Awal:</strong> Jika diisi, sistem akan otomatis membuat batch pertama</li>
                <li><strong>Batch Selanjutnya:</strong> Bisa ditambahkan melalui menu Pembelian dengan barcode & kadaluwarsa berbeda</li>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        {{-- Form Tambah Produk --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pen me-1"></i> Form Tambah Produk
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                            <input type="text" id="barcode" name="barcode" class="form-control"
                                value="{{ old('barcode') }}" placeholder="Scan barcode produk di sini" required autofocus
                                minlength="8" maxlength="50">
                            <small class="text-muted"><i class="fas fa-barcode"></i> Scan barcode produk (support EAN13,
                                CODE128, dll)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control"
                                value="{{ old('nama_produk') }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_beli_display" class="form-control" placeholder="0" required>
                                <input type="hidden" id="harga_beli" name="harga_beli" value="{{ old('harga_beli') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_jual_display" class="form-control" placeholder="0" required>
                                <input type="hidden" id="harga_jual" name="harga_jual" value="{{ old('harga_jual') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" id="stok" name="stok" class="form-control"
                                value="{{ old('stok', 0) }}" required min="0">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Jika diisi > 0, sistem akan otomatis membuat batch pertama
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kadaluwarsa" class="form-label">Tanggal Kadaluwarsa <span class="text-danger">*</span></label>
                            <input type="date" id="kadaluwarsa" name="kadaluwarsa" class="form-control"
                                value="{{ old('kadaluwarsa') }}" required>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> Tanggal kadaluwarsa untuk batch pertama
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="kategori_id" name="kategori_id" class="form-control" required>
                                <option disabled selected>-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->kategori_id }}"
                                        {{ old('kategori_id') == $kategori->kategori_id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="satuan_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select id="satuan_id" name="satuan_id" class="form-control" required>
                                <option disabled selected>-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->satuan_id }}"
                                        {{ old('satuan_id') == $satuan->satuan_id ? 'selected' : '' }}>
                                        {{ $satuan->nama_satuan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option disabled selected>-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                        {{ old('supplier_id') == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Foto Produk</label>
                            <input type="file" id="photo" name="photo" class="form-control"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Format: jpeg, png, jpg | Maksimal: 2MB</small>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-focus ke input barcode saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('barcode').focus();
            });

            // Auto-pindah ke nama produk setelah scan barcode (enter)
            document.getElementById('barcode').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('nama_produk').focus();
                }
            });

            // Format Rupiah otomatis untuk Harga Beli
            document.getElementById('harga_beli_display').addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, ''); // Ambil hanya angka
                document.getElementById('harga_beli').value = value; // Simpan angka mentah
                this.value = formatRupiah(value); // Tampilkan format Rupiah
            });

            // Format Rupiah otomatis untuk Harga Jual
            document.getElementById('harga_jual_display').addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, ''); // Ambil hanya angka
                document.getElementById('harga_jual').value = value; // Simpan angka mentah
                this.value = formatRupiah(value); // Tampilkan format Rupiah
            });

            // Fungsi format Rupiah (1000 jadi 1.000)
            function formatRupiah(angka) {
                if (!angka) return '';
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        </script>
    @endpush
@endsection