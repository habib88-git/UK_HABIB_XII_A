@extends('layout.master')
@section('title', 'Detail Pembelian')

@section('content')
    <div class="container-fluid py-4" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                {{-- Header --}}
                <div class="mb-4">
                    <h4 class="fw-bold text-primary mb-1">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Detail Pembelian
                    </h4>
                </div>

                {{-- Informasi Pembelian --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border-start border-primary border-3 ps-3">
                            <small class="text-muted d-block">Tanggal Pembelian</small>
                            <strong>
                                @php
                                    $tanggal = \Carbon\Carbon::parse($pembelian->tanggal);
                                    if ($tanggal->format('H:i:s') === '00:00:00') {
                                        echo $tanggal->translatedFormat('d F Y');
                                    } else {
                                        echo $tanggal->translatedFormat('d F Y H:i');
                                    }
                                @endphp
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-start border-success border-3 ps-3">
                            <small class="text-muted d-block">Admin</small>
                            <strong>{{ $pembelian->user->name ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Detail Produk --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-primary mb-0">
                            <i class="fas fa-boxes me-2"></i> Detail Produk
                        </h5>
                    </div>

                    <div class="table-responsive rounded">
                        <table class="table table-bordered align-middle">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>Produk</th>
                                    <th>Supplier</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Harga Beli (Rp)</th>
                                    <th>Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pembelian->details as $d)
                                    <tr class="table-row text-center">
                                        <td class="text-start">
                                            <div class="fw-semibold">{{ $d->produk->nama_produk ?? '-' }}</div>
                                            <small class="text-muted">Kode: {{ $d->produk->kode_produk ?? '-' }}</small>
                                        </td>
                                        <td>{{ $d->produk->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $d->produk->kategori->nama_kategori ?? '-' }}</td>
                                        <td>{{ $d->produk->satuan->nama_satuan ?? '-' }}</td>
                                        <td>{{ $d->jumlah }}</td>
                                        <td>Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                                        <td class="fw-bold text-success">Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-lg me-2"></i> Belum ada detail pembelian
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Total --}}
                <div class="card bg-light mb-4 border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-semibold">Total Pembelian:</h5>
                            <h4 class="mb-0 text-success fw-bold">
                                Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-start flex-wrap tombol-aksi mt-5">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <a href="{{ route('pembelian.edit', $pembelian->pembelian_id) }}" class="btn btn-outline-warning px-4">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-primary px-4">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Style --}}
    <style>
        .card {
            border-radius: 0.6rem;
        }

        .table th {
            font-weight: 600;
            vertical-align: middle;
            text-align: center;
        }

        .table-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .tombol-aksi {
            gap: 1.2rem;
        }

        .tombol-aksi .btn {
            margin-bottom: 0.5rem;
        }

        @media print {

            .btn,
            .tombol-aksi {
                display: none !important;
            }

            .card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
@endsection
