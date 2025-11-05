@extends('layout.master')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> Laporan Penjualan
        </h1>
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

    {{-- Filter Card --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-semibold">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="pelanggan_id" class="form-label fw-semibold">Pelanggan</label>
                    <select name="pelanggan_id" id="pelanggan_id" class="form-control">
                        <option value="">Semua Pelanggan</option>
                        @foreach ($pelanggans as $pelanggan)
                            <option value="{{ $pelanggan->pelanggan_id }}" {{ request('pelanggan_id') == $pelanggan->pelanggan_id ? 'selected' : '' }}>
                                {{ $pelanggan->nama_pelanggan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="kasir_id" class="form-label fw-semibold">Kasir</label>
                    <select name="kasir_id" id="kasir_id" class="form-control">
                        <option value="">Semua Kasir</option>
                        @foreach ($kasirs as $kasir)
                            <option value="{{ $kasir->user_id }}" {{ request('kasir_id') == $kasir->user_id ? 'selected' : '' }}>
                                {{ $kasir->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol --}}
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <a href="{{ route('laporan.cetak', request()->all()) }}" target="_blank" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Data Penjualan</h6>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-bordered align-items-center table-hover" id="laporanTable">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width:5%">No</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total Harga</th>
                                <th>Metode</th>
                                <th>Kasir</th>
                                <th style="width:10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualans as $p)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}</td>
                                    <td>{{ optional($p->pelanggan)->nama_pelanggan ?? 'Umum' }}</td>
                                    <td class="text-end">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $p->pembayaran->metode ?? '-' }}</td>
                                    <td>{{ $p->user->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('laporan.struk', $p->penjualan_id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-success" data-toggle="tooltip" title="Cetak Struk">
                                            <i class="fas fa-print"></i>
                                        </a>
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

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#laporanTable').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" },
            "pageLength": 10,
            "lengthChange": false,
            "searching": false,
            "columnDefs": [{ "orderable": false, "targets": 6 }]
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
