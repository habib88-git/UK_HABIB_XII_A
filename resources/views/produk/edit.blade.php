@extends('layout.master')

@section('title', 'Edit Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-box-open me-2"></i> Edit Produk
            </h3>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Form Edit Produk --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('produk.update', $produk->produk_id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" name="barcode" id="barcode" class="form-control"
                                value="{{ old('barcode', $produk->barcode) }}" maxlength="50" readonly>
                            <small class="text-muted">Barcode tidak dapat diubah</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" id="nama_produk" class="form-control"
                                value="{{ old('nama_produk', $produk->nama_produk) }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli</label>
                            <input type="number" name="harga_beli" id="harga_beli" class="form-control"
                                value="{{ old('harga_beli', $produk->harga_beli) }}" required step="0.01" min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual</label>
                            <input type="number" name="harga_jual" id="harga_jual" class="form-control"
                                value="{{ old('harga_jual', $produk->harga_jual) }}" required step="0.01" min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" name="stok" id="stok" class="form-control"
                                value="{{ old('stok', $produk->stok) }}" required min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kadaluwarsa" class="form-label">Tanggal Kadaluwarsa</label>
                            <input type="date" name="kadaluwarsa" id="kadaluwarsa" class="form-control"
                                value="{{ old('kadaluwarsa', $produk->kadaluwarsa->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select name="kategori_id" id="kategori_id" class="form-control" required>
                                <option value="" disabled>-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->kategori_id }}"
                                        {{ old('kategori_id', $produk->kategori_id) == $kategori->kategori_id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="satuan_id" class="form-label">Satuan</label>
                            <select name="satuan_id" id="satuan_id" class="form-control" required>
                                <option value="" disabled>-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->satuan_id }}"
                                        {{ old('satuan_id', $produk->satuan_id) == $satuan->satuan_id ? 'selected' : '' }}>
                                        {{ $satuan->nama_satuan }}
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
                            <input type="file" name="photo" id="photo" class="form-control"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Upload foto baru jika ingin mengganti. Maksimal 2MB.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barcode Visual</label>
                            <div class="border p-3 rounded text-center bg-light">
                                {!! DNS1D::getBarcodeHTML($produk->barcode, 'EAN13', 2, 60) !!}
                                <div class="mt-2">{{ $produk->barcode }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Update Produk
                        </button>
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
