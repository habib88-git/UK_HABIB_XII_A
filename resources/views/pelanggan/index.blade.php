@extends('layout.master')
@section('title', 'Daftar Pelanggan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-user-friends"></i> Daftar Pelanggan
        </h3>

        {{-- Tombol tambah pelanggan hanya untuk kasir --}}
        @if (auth()->user()->role === 'kasir')
            <a href="{{ route('pelanggan.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-user-plus"></i> Tambah Pelanggan
            </a>
        @endif
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

                            {{-- Kolom aksi hanya untuk admin --}}
                            @if (auth()->user()->role === 'admin')
                                <th style="width: 12%">Aksi</th>
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
                                        <div class="d-flex justify-content-center gap-1">
                                            {{-- Edit --}}
                                            <a href="{{ route('pelanggan.edit', $p->pelanggan_id) }}"
                                               class="btn btn-sm btn-outline-warning" data-toggle="tooltip"
                                               title="Edit Pelanggan">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- Hapus --}}
                                            <form action="{{ route('pelanggan.destroy', $p->pelanggan_id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-toggle="tooltip" title="Hapus Pelanggan">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
@endsection

@section('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
