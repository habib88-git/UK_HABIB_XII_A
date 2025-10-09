@extends('layout.master')
@section('title', 'Daftar Kategori')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-tags"></i> Daftar Kategori
        </h3>
        <a href="{{ route('kategori.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus"></i> Tambah Kategori
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
                            <th>Nama Kategori</th>
                            <th style="width: 12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kategori)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $kategori->nama_kategori }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('kategori.edit', $kategori->kategori_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip" title="Edit Kategori">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('kategori.destroy', $kategori->kategori_id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-toggle="tooltip" title="Hapus Kategori">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Belum ada data kategori
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
