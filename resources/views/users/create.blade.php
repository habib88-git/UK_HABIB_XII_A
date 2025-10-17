@extends('layout.master')

@section('title', 'Tambah User')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-user-plus me-2"></i> Tambah User
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

    {{-- Form Tambah User --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="{{ old('email') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="no_telp" class="form-label">Telp</label>
                        <input type="text" id="no_telp" name="no_telp" class="form-control" 
                               value="{{ old('no_telp') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sandi" class="form-label">Password</label>
                        <input type="password" id="sandi" name="sandi" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="role" class="form-label">Akses</label>
                        <select id="role" name="role" class="form-control" required>
                            <option>-- Pilih Akses --</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
