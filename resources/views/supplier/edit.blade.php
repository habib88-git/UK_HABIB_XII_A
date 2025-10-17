@extends('layout.master')

@section('title', 'Edit Supplier')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-truck-loading"></i> Edit Supplier
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

        {{-- Form Edit Supplier --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('supplier.update', $supplier->supplier_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_supplier" class="form-label">Nama Supplier</label>
                            <input type="text" id="nama_supplier" name="nama_supplier" class="form-control"
                                value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="telp" class="form-label">Telepon</label>
                            <input type="number" id="telp" name="telp" class="form-control"
                                value="{{ old('telp', $supplier->telp) }}" required>
                        </div>

                        <div class="col-12 mb-4">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea id="alamat" name="alamat" class="form-control" rows="3" required>{{ old('alamat', $supplier->alamat) }}</textarea>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i> Update Supplier
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
