@extends('layout.master')
@section('title', 'History Stock - ' . $produk->nama_produk)

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-history"></i> History Stock
                </h1>
                <p class="text-muted mb-0">{{ $produk->nama_produk }}</p>
            </div>
            <a href="{{ route('stock-history.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Info Produk --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        @if($produk->photo)
                            <img src="{{ asset('storage/' . $produk->photo) }}" alt="{{ $produk->nama_produk }}" 
                                 class="img-fluid rounded shadow-sm" style="max-height: 150px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm" 
                                 style="height: 150px;">
                                <i class="fas fa-image text-muted fa-3x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-10">
                        <h4 class="font-weight-bold mb-3 text-primary">{{ $produk->nama_produk }}</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-2 text-muted small">Barcode</p>
                                <div class="barcode-display mb-2">
                                    @php
                                        try {
                                            echo DNS1D::getBarcodeHTML($produk->barcode, 'C128', 1.5, 40);
                                        } catch (\Exception $e) {
                                            echo '<small class="text-muted">Barcode Error</small>';
                                        }
                                    @endphp
                                </div>
                                <code class="d-block text-center">{{ $produk->barcode }}</code>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-2 text-muted small">Kategori</p>
                                <span class="badge badge-info badge-lg">{{ $produk->kategori->nama_kategori ?? '-' }}</span>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-2 text-muted small">Satuan</p>
                                <span class="badge badge-secondary badge-lg">{{ $produk->satuan->nama_satuan ?? '-' }}</span>
                            </div>
                            <div class="col-md-2">
                                <p class="mb-2 text-muted small">Supplier</p>
                                <p class="mb-0 font-weight-semibold">{{ $produk->supplier->nama_supplier ?? '-' }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-2 text-muted small">Stok Saat Ini</p>
                                <h3 class="mb-0">
                                    <span class="badge badge-{{ $produk->stok > 10 ? 'success' : ($produk->stok > 0 ? 'warning' : 'danger') }}" style="font-size: 1.2rem;">
                                        {{ number_format($produk->stok) }} {{ $produk->satuan->nama_satuan ?? '' }}
                                    </span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-left-success shadow h-100 py-3">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Stok Masuk
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    <i class="fas fa-plus-circle text-success mr-1"></i>
                                    {{ number_format($histories->where('tipe', 'masuk')->sum('jumlah')) }} {{ $produk->satuan->nama_satuan ?? '' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-down fa-3x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-danger shadow h-100 py-3">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Stok Keluar
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    <i class="fas fa-minus-circle text-danger mr-1"></i>
                                    {{ number_format($histories->where('tipe', 'keluar')->sum('jumlah')) }} {{ $produk->satuan->nama_satuan ?? '' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-up fa-3x text-danger opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-left-info shadow h-100 py-3">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Transaksi
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    <i class="fas fa-list-alt text-info mr-1"></i>
                                    {{ number_format($histories->total()) }} Transaksi
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-3x text-info opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-2"></i> Riwayat Transaksi
                </h6>
            </div>
            <div class="table-responsive p-3">
                <table class="table table-bordered align-items-center table-hover">
                    <thead class="thead-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Tanggal & Waktu</th>
                            <th width="10%">Tipe</th>
                            <th width="10%">Jumlah</th>
                            <th width="10%">Stok Awal</th>
                            <th width="10%">Stok Akhir</th>
                            <th>Keterangan</th>
                            <th width="12%">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)
                            <tr>
                                <td class="text-center">{{ $histories->firstItem() + $loop->index }}</td>
                                <td class="text-center">
                                    <div class="fw-semibold">
                                        {{ \Carbon\Carbon::parse($history->created_at)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                                    </div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($history->created_at)->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($history->tipe == 'masuk')
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-arrow-down"></i> Masuk
                                        </span>
                                    @else
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-arrow-up"></i> Keluar
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">
                                    <span class="text-{{ $history->tipe == 'masuk' ? 'success' : 'danger' }}" style="font-size: 1.1rem;">
                                        {{ $history->tipe == 'masuk' ? '+' : '-' }}{{ number_format($history->jumlah) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ number_format($history->stok_sebelum) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ number_format($history->stok_sesudah) }}</span>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <small class="text-dark">{{ $history->keterangan ?? '-' }}</small>
                                    </div>
                                    @if($history->referensi_tipe && $history->referensi_id)
                                        <span class="badge badge-light">
                                            <i class="fas fa-link"></i> 
                                            {{ ucfirst($history->referensi_tipe) }} #{{ $history->referensi_id }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-info-circle"></i> Manual/System
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <i class="fas fa-user-circle text-muted"></i>
                                    {{ $history->user->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Belum ada history untuk produk ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($histories->hasPages())
                <div class="card-footer">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .table td {
            vertical-align: middle;
        }
        code {
            font-size: 0.9em;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .badge-lg {
            padding: 6px 12px;
            font-size: 0.9rem;
        }
        .fw-semibold {
            font-weight: 600;
        }
        .border-left-success {
            border-left: 5px solid #1cc88a !important;
        }
        .border-left-danger {
            border-left: 5px solid #e74a3b !important;
        }
        .border-left-info {
            border-left: 5px solid #36b9cc !important;
        }
        .opacity-50 {
            opacity: 0.3;
        }
        .barcode-display {
            display: flex;
            justify-content: center;
        }
        .barcode-display img {
            max-width: 150px;
            height: auto;
        }
        .font-weight-semibold {
            font-weight: 600;
        }
    </style>
@endpush