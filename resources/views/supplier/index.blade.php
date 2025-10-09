@extends('layout.master')

@section('title', 'Daftar Supplier')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-truck-loading"></i> Daftar Supplier
        </h3>
        <a href="{{ route('supplier.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus"></i> Tambah Supplier
        </a>
    </div>

    {{-- Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th style="width: 12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $s)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $s->nama_supplier }}</td>
                                <td>{{ $s->alamat }}</td>
                                <td>{{ $s->no_telp }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('supplier.edit', $s->supplier_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip" title="Edit Supplier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('supplier.destroy', $s->supplier_id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-toggle="tooltip" title="Hapus Supplier">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Belum ada data supplier
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
