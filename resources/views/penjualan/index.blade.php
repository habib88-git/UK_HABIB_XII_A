@extends('layout.master')
@section('title', 'Daftar Penjualan')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-primary">
                <i class="fas fa-shopping-cart"></i> Daftar Penjualan
            </h3>
            <a href="{{ route('penjualan.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-plus"></i> Tambah Penjualan
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
                                <th>Pelanggan</th>
                                <th style="width: 15%">Total</th>
                                <th style="width: 15%">Metode</th>
                                <th style="width: 15%">Kasir</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penjualans as $p)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}
                                    </td>
                                    <td>{{ $p->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                    <td class="fw-bold text-end">
                                        @if ($p->diskon > 0)
                                            <div>
                                                <span class="text-success">Rp
                                                    {{ number_format($p->total_harga - $p->diskon, 0, ',', '.') }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    <s>Rp {{ number_format($p->total_harga, 0, ',', '.') }}</s>
                                                </small>
                                            </div>
                                        @else
                                            Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $p->pembayaran->metode ?? '-' }}</td>
                                    <td>{{ $p->user->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('penjualan.show', $p->penjualan_id) }}"
                                            class="btn btn-sm btn-outline-info" data-toggle="tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
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
@endsection
