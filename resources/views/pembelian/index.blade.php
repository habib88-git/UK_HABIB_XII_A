@extends('layout.master')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-file-invoice-dollar"></i> Daftar Pembelian
        </h3>
        <a href="{{ route('pembelian.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus"></i> Tambah Pembelian
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
                            <th style="width: 15%">Tanggal</th>
                            <th style="width: 20%">Total Harga</th>
                            <th>Supplier</th>
                            <th>User</th>
                            <th style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pembelians as $p)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    {{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}
                                </td>
                                <td class="fw-bold text-end">
                                    Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                </td>
                                <td>{{ $p->supplier->nama_supplier ?? '-' }}</td>
                                <td>{{ $p->user->name ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Detail --}}
                                        <a href="{{ route('pembelian.show', $p->pembelian_id) }}"
                                           class="btn btn-sm btn-outline-info"
                                           data-toggle="tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('pembelian.edit', $p->pembelian_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip" title="Edit Pembelian">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('pembelian.destroy', $p->pembelian_id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus pembelian ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-toggle="tooltip" title="Hapus Pembelian">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Belum ada data pembelian
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
