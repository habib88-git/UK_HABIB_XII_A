@extends('layout.master')

@section('title', 'Tambah Pelanggan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-user-plus me-2"></i> Tambah Pelanggan
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

    {{-- Form Tambah Pelanggan --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('pelanggan.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_pelanggan" class="form-label">Nama Pelanggan</label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" 
                               class="form-control" value="{{ old('nama_pelanggan') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="text" id="nomor_telepon" name="nomor_telepon" 
                               class="form-control" value="{{ old('nomor_telepon') }}" required>
                    </div>

                    <div class="col-md-12 mb-4">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3" required>{{ old('alamat') }}</textarea>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
