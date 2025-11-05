@extends('layout.master')

@section('title', 'Edit Pelanggan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-edit"></i> Edit Pelanggan
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

        {{-- Form Edit Pelanggan --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pen me-1"></i> Form Edit Pelanggan
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('pelanggan.update', $pelanggan->pelanggan_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_pelanggan" class="form-label">Nama Pelanggan <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nama_pelanggan" name="nama_pelanggan" class="form-control"
                                value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}"
                                placeholder="Masukkan nama pelanggan" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nomor_telepon" class="form-label">Nomor Telepon <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nomor_telepon" name="nomor_telepon" class="form-control"
                                value="{{ old('nomor_telepon', $pelanggan->nomor_telepon) }}"
                                placeholder="Masukkan nomor telepon" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea id="alamat" name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap"
                                required>{{ old('alamat', $pelanggan->alamat) }}</textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
