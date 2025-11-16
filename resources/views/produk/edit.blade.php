@extends('layout.master')
@section('title', 'Edit Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Edit Produk
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

        {{-- Form Edit Produk --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pen me-1"></i> Form Edit Produk
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('produk.update', $produk->produk_id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" id="barcode" name="barcode" class="form-control"
                                value="{{ old('barcode', $produk->barcode) }}" readonly>
                            <small class="text-muted">Barcode tidak dapat diubah</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control"
                                value="{{ old('nama_produk', $produk->nama_produk) }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_beli_display" class="form-control"
                                    value="{{ number_format(old('harga_beli', $produk->harga_beli), 0, ',', '.') }}"
                                    placeholder="0">
                                <input type="hidden" id="harga_beli" name="harga_beli"
                                    value="{{ old('harga_beli', $produk->harga_beli) }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_jual_display" class="form-control"
                                    value="{{ number_format(old('harga_jual', $produk->harga_jual), 0, ',', '.') }}"
                                    placeholder="0">
                                <input type="hidden" id="harga_jual" name="harga_jual"
                                    value="{{ old('harga_jual', $produk->harga_jual) }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control"
                                value="{{ old('stok', $produk->stok) }}" required min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kadaluwarsa" class="form-label">Tanggal Kadaluwarsa</label>
                            <input type="date" id="kadaluwarsa" name="kadaluwarsa" class="form-control"
                                value="{{ old('kadaluwarsa', $produk->kadaluwarsa->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select id="kategori_id" name="kategori_id" class="form-control" required>
                                <option disabled>-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->kategori_id }}"
                                        {{ old('kategori_id', $produk->kategori_id) == $kategori->kategori_id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="satuan_id" class="form-label">Satuan</label>
                            <select id="satuan_id" name="satuan_id" class="form-control" required>
                                <option disabled>-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->satuan_id }}"
                                        {{ old('satuan_id', $produk->satuan_id) == $satuan->satuan_id ? 'selected' : '' }}>
                                        {{ $satuan->nama_satuan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option disabled>-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                        {{ old('supplier_id', $produk->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Foto Produk</label>
                            @if ($produk->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $produk->photo) }}" alt="{{ $produk->nama_produk }}"
                                        width="120" style="object-fit: cover; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" id="photo" name="photo" class="form-control"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Upload foto baru jika ingin mengganti. Maksimal 2MB.</small>
                        </div>
                    </div>

                    {{-- Barcode Visual (Full Width) --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-center d-block">Barcode Visual</label>
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    @php
                                        try {
                                            echo '<div class="mb-3 d-flex justify-content-center">';
                                            echo DNS1D::getBarcodeHTML($produk->barcode, 'C128', 2.5, 80);
                                            echo '</div>';
                                            echo '<h5 class="mb-2">' . $produk->barcode . '</h5>';
                                            echo '<small class="text-muted">Format: CODE128</small>';
                                        } catch (\Exception $e) {
                                            echo '<div class="text-danger">Barcode Error: ' .
                                                $produk->barcode .
                                                '</div>';
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                        <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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

        // Fungsi format Rupiah
        function formatRupiah(angka) {
            if (!angka) return '0';
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
@endpush
