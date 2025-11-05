@extends('layout.master')

@section('title', 'Detail Pembelian')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="fas fa-file-invoice me-2"></i> Detail Pembelian
            </h3>
            <div class="text-muted">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ \Carbon\Carbon::parse($pembelian->tanggal)->translatedFormat('l, d F Y') }}
            </div>
        </div>

        {{-- Card Informasi Pembelian --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> Informasi Pembelian
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary mb-1">
                                <i class="fas fa-calendar-day me-1"></i> Tanggal Pembelian
                            </label>
                            <p class="mb-0 fs-6">
                                {{ \Carbon\Carbon::parse($pembelian->tanggal)->translatedFormat('d F Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary mb-1">
                                <i class="fas fa-truck me-1"></i> Supplier
                            </label>
                            <p class="mb-0 fs-6">{{ $pembelian->supplier->nama_supplier ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary mb-1">
                                <i class="fas fa-user me-1"></i> Admin
                            </label>
                            <p class="mb-0 fs-6">{{ $pembelian->user->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-primary mb-1">
                                <i class="fas fa-receipt me-1"></i> Total Pembelian
                            </label>
                            <p class="mb-0 fs-6 fw-bold text-success">Rp
                                {{ number_format($pembelian->total_harga, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Detail Produk --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i> Detail Produk
                    </h5>
                    <span class="badge bg-light text-primary fs-6">
                        {{ $pembelian->details->count() }} Item
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 text-center">No</th>
                                <th class="py-3">Produk</th>
                                <th class="py-3 text-center">Jumlah</th>
                                <th class="py-3 text-end">Harga Beli</th>
                                <th class="py-3 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pembelian->details as $index => $d)
                                <tr class="table-row">
                                    <td class="text-center fw-semibold">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-cube me-2 text-muted"></i>
                                            <span>{{ $d->produk->nama_produk ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill fs-6">
                                            {{ $d->jumlah }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($d->harga_beli, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3"></i>
                                            <p class="mb-0 fs-5">Belum ada detail pembelian</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($pembelian->details->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold py-3">Total Pembelian:</td>
                                    <td class="text-end fw-bold fs-5 text-success py-3">
                                        Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
            <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
            <div>
                <button onclick="window.print()" class="btn btn-outline-primary me-2">
                    <i class="fas fa-print me-2"></i> Cetak
                </button>
                <a href="{{ route('pembelian.edit', $pembelian->pembelian_id) }}" class="btn btn-outline-warning">
                    <i class="fas fa-edit me-2"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 0.75rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table td,
        .table th {
            padding: 0.75rem;
            vertical-align: middle;
        }

        .table-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .btn {
            border-radius: 0.5rem;
        }

        .badge {
            font-size: 0.75em;
        }

        .form-label {
            margin-bottom: 0.25rem;
        }

        @media print {

            .btn,
            .card-header {
                display: none !important;
            }

            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection
