@extends('layout.master')
@section('title', 'Daftar Satuan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-balance-scale"></i> Daftar Satuan
        </h1>
        <a href="{{ route('satuan.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Satuan
        </a>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
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
            <div class="card mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Data Satuan</h6>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-bordered align-items-center table-hover" id="dataTableHover">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Nama Satuan</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($satuans as $satuan)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $satuan->nama_satuan }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('satuan.edit', $satuan->satuan_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip" title="Edit Satuan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('satuan.destroy', $satuan->satuan_id) }}"
                                              method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('Yakin ingin menghapus satuan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-toggle="tooltip" title="Hapus Satuan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> Belum ada data satuan
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
    $(document).ready(function () {
        $('#dataTableHover').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
