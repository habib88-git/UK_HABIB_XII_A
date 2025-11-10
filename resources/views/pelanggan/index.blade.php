@extends('layout.master')
@section('title', 'Daftar Pelanggan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user-friends"></i> Daftar Pelanggan
            </h1>

            {{-- Tombol tambah pelanggan hanya untuk kasir --}}
            @if (auth()->user()->role === 'kasir')
                <a href="{{ route('pelanggan.create') }}" class="btn btn-success shadow-sm">
                    <i class="fas fa-user-plus fa-sm text-white-50"></i> Tambah Pelanggan
                </a>
            @endif
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-check-circle"></i></strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- DataTable Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Data Pelanggan</h6>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-bordered table-hover align-middle" id="dataTableHover">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Telepon</th>
                                    {{-- Kolom aksi hanya untuk admin --}}
                                    @if (auth()->user()->role === 'admin')
                                        <th style="width: 20%">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pelanggans as $p)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $p->nama_pelanggan }}</td>
                                        <td>{{ $p->alamat }}</td>
                                        <td>{{ $p->nomor_telepon }}</td>

                                        {{-- Tombol aksi hanya muncul untuk admin --}}
                                        @if (auth()->user()->role === 'admin')
                                            <td class="text-center">
                                                {{-- Tombol Lihat --}}
                                                <a href="{{ route('pelanggan.show', $p->pelanggan_id) }}"
                                                    class="btn btn-sm btn-outline-info" data-toggle="tooltip"
                                                    title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('pelanggan.edit', $p->pelanggan_id) }}"
                                                    class="btn btn-sm btn-outline-warning" data-toggle="tooltip"
                                                    title="Edit Pelanggan">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('pelanggan.destroy', $p->pelanggan_id) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        data-toggle="tooltip" title="Hapus Pelanggan">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role === 'admin' ? 5 : 4 }}"
                                            class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Belum ada data pelanggan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#dataTableHover').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });

            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
