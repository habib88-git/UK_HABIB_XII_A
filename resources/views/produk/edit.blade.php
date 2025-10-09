@extends('layout.master')

@section('title', 'Edit Produk')

@section('content')
<div class="container-fluid" id="container-wrapper">
    <h2 class="mb-4">Edit Produk</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('produk.update', $produk->produk_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="form-control"
                   value="{{ old('nama_produk', $produk->nama_produk) }}" required maxlength="100">
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Foto Produk (jpeg, png, jpg max 2MB)</label>
            @if($produk->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $produk->photo) }}"
                         alt="{{ $produk->nama_produk }}" width="100"
                         style="object-fit: cover; border-radius: 5px;">
                </div>
            @endif
            <input type="file" name="photo" id="photo" class="form-control"
                   accept="image/jpeg,image/png,image/jpg">
            <small class="form-text text-muted">Upload foto baru jika ingin mengganti.</small>
        </div>

        <div class="mb-3">
            <label for="harga_beli" class="form-label">Harga Beli</label>
            <input type="number" name="harga_beli" id="harga_beli" class="form-control"
                   value="{{ old('harga_beli', $produk->harga_beli) }}" required step="0.01" min="0">
        </div>

        <div class="mb-3">
            <label for="harga_jual" class="form-label">Harga Jual</label>
            <input type="number" name="harga_jual" id="harga_jual" class="form-control"
                   value="{{ old('harga_jual', $produk->harga_jual) }}" required step="0.01" min="0">
        </div>

        <div class="mb-3">
            <label for="stok" class="form-label">Stok</label>
            <input type="number" name="stok" id="stok" class="form-control"
                   value="{{ old('stok', $produk->stok) }}" required min="0">
        </div>

        <div class="mb-3">
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

        <div class="mb-3">
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

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" style="margin-right: 5px">Update Produk</button>
            <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
