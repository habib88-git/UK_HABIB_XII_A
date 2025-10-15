@extends('layout.master')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-chart-line"></i> Laporan Penjualan
        </h3>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-semibold">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date"
                           value="{{ request('start_date') }}"
                           class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date"
                           value="{{ request('end_date') }}"
                           class="form-control">
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-start gap-2">
                    {{-- Tombol Filter --}}
                    <button type="submit" class="btn btn-outline-primary" data-toggle="tooltip" title="Filter Data">
                        <i class="fas fa-filter"></i>
                    </button>
                    {{-- Tombol Reset --}}
                    <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary" data-toggle="tooltip" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                    {{-- Tombol Cetak PDF --}}
                    <a href="{{ route('laporan.cetak', request()->all()) }}" target="_blank"
                       class="btn btn-outline-danger" data-toggle="tooltip" title="Cetak PDF">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:15%">Tanggal</th>
                            <th style="width:20%">Pelanggan</th>
                            <th style="width:15%">Total Harga</th>
                            <th style="width:15%">Metode</th>
                            <th style="width:15%">User</th>
                            <th style="width:10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualans as $p)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}</td>
                                <td>{{ optional($p->pelanggan)->nama_pelanggan ?? 'Umum' }}</td>
                                <td class="text-end">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $p->pembayaran->metode ?? '-' }}</td>
                                <td>{{ $p->user->name ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Struk --}}
                                        <a href="{{ route('laporan.struk', $p->penjualan_id) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-success"
                                           data-toggle="tooltip" title="Cetak Struk">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Belum ada data penjualan
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
