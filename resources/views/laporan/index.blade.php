@extends('layout.master')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">📊 Laporan Penjualan</h2>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-3">
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
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <a href="{{ route('laporan.cetak', request()->all()) }}" target="_blank" 
                       class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width:5%">No</th>
                            <th style="width:15%">Tanggal</th>
                            <th style="width:20%">Pelanggan</th>
                            <th style="width:15%">Total Harga</th>
                            <th style="width:15%">Metode</th>
                            <th style="width:15%">User</th>
                            <th style="width:15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualans as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}</td>
                            <td>
                                {{ optional($p->pelanggan)->nama_pelanggan ?? 'Umum' }}
                            </td>
                            <td class="text-end">Rp {{ number_format($p->total_harga,0,',','.') }}</td>
                            <td class="text-center">{{ $p->pembayaran->metode ?? '-' }}</td>
                            <td>{{ $p->user->name ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('laporan.struk', $p->penjualan_id) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-printer"></i> Struk
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <em>Belum ada data penjualan</em>
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
