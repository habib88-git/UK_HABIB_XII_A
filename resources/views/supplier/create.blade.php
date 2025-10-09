@extends('layout.master')

@section('title', 'Tambah Supplier')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-truck me-2"></i> Tambah Supplier
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

    {{-- Form Tambah Supplier --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('supplier.store') }}" method="POST">
                @csrf
                <div class="row">
                    {{-- Nama Supplier --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_supplier" class="form-label">Nama Supplier</label>
                        <input type="text" id="nama_supplier" name="nama_supplier" 
                               class="form-control" value="{{ old('nama_supplier') }}" required>
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="col-md-6 mb-3">
                        <label for="no_telp" class="form-label">Nomor Telepon</label>
                        <input type="text" id="no_telp" name="no_telp" 
                               class="form-control" value="{{ old('no_telp') }}" 
                               required maxlength="15" pattern="[0-9]+" title="Hanya boleh angka">
                    </div>

                    {{-- Alamat --}}
                    <div class="col-md-12 mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea id="alamat" name="alamat" 
                                  class="form-control" rows="3" required>{{ old('alamat') }}</textarea>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('supplier.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
