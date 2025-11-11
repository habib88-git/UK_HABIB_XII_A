@extends('layout.master')

@section('title', 'Tambah Supplier')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-truck-loading"></i> Tambah Supplier
        </h3>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Form Tambah Supplier --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-pen me-1"></i> Form Tambah Supplier
            </h6>
        </div>

        <div class="card-body">
            <form action="{{ route('supplier.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- Nama Supplier --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_supplier" class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" id="nama_supplier" name="nama_supplier" class="form-control"
                               value="{{ old('nama_supplier') }}" placeholder="Masukkan nama supplier" required maxlength="100">
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="col-md-6 mb-3">
                        <label for="no_telp" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" id="no_telp" name="no_telp" class="form-control"
                               value="{{ old('no_telp') }}" placeholder="Masukkan nomor telepon" required maxlength="15" pattern="[0-9]+"
                               title="Hanya boleh angka">
                    </div>

                    {{-- Alamat --}}
                    <div class="col-md-12 mb-3">
                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap" required>{{ old('alamat') }}</textarea>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
