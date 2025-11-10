@extends('layout.master')

@section('title', 'Tambah Pelanggan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-plus"></i> Tambah Pelanggan</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-pen me-1"></i> Form Tambah Pelanggan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('pelanggan.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" class="form-control" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2" required></textarea>
                        </div>

                        {{-- Dropdown Wilayah --}}
                        <div class="col-md-3 mb-3">
                            <label>Provinsi</label>
                            <select id="province_id" name="province_id" class="form-control" required>
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach ($provinces as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Kota/Kabupaten</label>
                            <select id="city_id" name="city_id" class="form-control" required>
                                <option value="">-- Pilih Kota --</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Kecamatan</label>
                            <select id="district_id" name="district_id" class="form-control" required>
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Kelurahan/Desa</label>
                            <select id="village_id" name="village_id" class="form-control" required>
                                <option value="">-- Pilih Kelurahan --</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-save me-1"></i>
                            Simpan</button>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary"><i
                                class="fas fa-times me-1"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $('#province_id').on('change', function() {
            var provinceID = $(this).val();
            $('#city_id').empty().append('<option>Loading...</option>');
            $.get('/api/cities/' + provinceID, function(data) {
                $('#city_id').empty().append('<option value="">-- Pilih Kota --</option>');
                $.each(data, function(key, value) {
                    $('#city_id').append('<option value="' + key + '">' + value + '</option>');
                });
            });
        });

        $('#city_id').on('change', function() {
            var cityID = $(this).val();
            $('#district_id').empty().append('<option>Loading...</option>');
            $.get('/api/districts/' + cityID, function(data) {
                $('#district_id').empty().append('<option value="">-- Pilih Kecamatan --</option>');
                $.each(data, function(key, value) {
                    $('#district_id').append('<option value="' + key + '">' + value + '</option>');
                });
            });
        });

        $('#district_id').on('change', function() {
            var districtID = $(this).val();
            $('#village_id').empty().append('<option>Loading...</option>');
            $.get('/api/villages/' + districtID, function(data) {
                $('#village_id').empty().append('<option value="">-- Pilih Kelurahan --</option>');
                $.each(data, function(key, value) {
                    $('#village_id').append('<option value="' + key + '">' + value + '</option>');
                });
            });
        });
    </script>
@endpush
