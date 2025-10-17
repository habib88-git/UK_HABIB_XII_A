@extends('layout.master')

@section('title', 'Tambah Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-box me-2"></i> Tambah Produk
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

        {{-- Form Tambah Produk --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk</label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control"
                                value="{{ old('nama_produk') }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control"
                                value="{{ old('stok', 0) }}" required min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli</label>
                            <input type="number" id="harga_beli" name="harga_beli" class="form-control"
                                value="{{ old('harga_beli') }}" required step="0.01" min="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual</label>
                            <input type="number" id="harga_jual" name="harga_jual" class="form-control"
                                value="{{ old('harga_jual') }}" required step="0.01" min="0">
                        </div>



                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
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

                        <div class="col-md-6 mb-4">
                            <label for="satuan_id" class="form-label">Satuan</label>
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
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="photo" class="form-label">Foto Produk</label>
                        <input type="file" id="photo" name="photo" class="form-control"
                            accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted">Format: jpeg, png, jpg | Maksimal: 2MB</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Simpan Produk
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
