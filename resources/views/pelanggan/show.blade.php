@extends('layout.master')

@section('title', 'Detail Pelanggan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user"></i> Detail Pelanggan
            </h1>
        </div>

        {{-- Card Detail --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-1"></i> Informasi Pelanggan
                </h6>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Nama Pelanggan</label>
                        <div class="form-control bg-light">{{ $pelanggan->nama_pelanggan }}</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Nomor Telepon</label>
                        <div class="form-control bg-light">{{ $pelanggan->nomor_telepon }}</div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <div class="form-control bg-light">{{ $pelanggan->alamat }}</div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Provinsi</label>
                        <div class="form-control bg-light">
                            {{ \Laravolt\Indonesia\Models\Province::where('code', $pelanggan->province_id)->first()?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Kota/Kabupaten</label>
                        <div class="form-control bg-light">
                            {{ \Laravolt\Indonesia\Models\City::where('code', $pelanggan->city_id)->first()?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Kecamatan</label>
                        <div class="form-control bg-light">
                            {{ \Laravolt\Indonesia\Models\District::where('code', $pelanggan->district_id)->first()?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Kelurahan/Desa</label>
                        <div class="form-control bg-light">
                            {{ \Laravolt\Indonesia\Models\Village::where('code', $pelanggan->village_id)->first()?->name ?? '-' }}
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="text-end mt-4">
                    <a href="{{ route('pelanggan.edit', $pelanggan->pelanggan_id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

    </div>
@endsection
