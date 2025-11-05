@extends('layout.master')

@section('title', 'Edit Satuan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-balance-scale me-2"></i> Edit Satuan
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

        {{-- Form Edit Satuan --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('satuan.update', $satuan->satuan_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama_satuan" class="form-label">Nama Satuan</label>
                            <input type="text" id="nama_satuan" name="nama_satuan" class="form-control"
                                value="{{ old('nama_satuan', $satuan->nama_satuan) }}" required>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                        <a href="{{ route('satuan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
