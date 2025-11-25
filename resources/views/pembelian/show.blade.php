@extends('layout.master')

@section('title', 'Detail Pembelian')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 print-header">
            <div>
                <h3 class="text-primary mb-1">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Detail Pembelian
                </h3>
                <small class="text-muted">
                    No. Invoice: #{{ str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) }}
                </small>
            </div>
        </div>

        {{-- Card Detail --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> Informasi Pembelian
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="info-box p-3 h-100">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="40%" class="text-muted">
                                        <i class="fas fa-calendar-day me-2 text-primary"></i>Tanggal
                                    </td>
                                    <td width="5%">:</td>
                                    <td class="fw-semibold">
                                        @php
                                            $tanggal = \Carbon\Carbon::parse($pembelian->tanggal);
                                            if ($tanggal->format('H:i:s') === '00:00:00') {
                                                echo $tanggal->translatedFormat('d F Y');
                                            } else {
                                                echo $tanggal->translatedFormat('d F Y, H:i');
                                            }
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">
                                        <i class="fas fa-user me-2 text-success"></i>Admin
                                    </td>
                                    <td>:</td>
                                    <td class="fw-semibold">{{ $pembelian->user->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box p-3 h-100">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">
                                        <i class="fas fa-boxes me-2 text-warning"></i>Total Item
                                    </td>
                                    <td>:</td>
                                    <td class="fw-semibold">{{ $pembelian->details->count() }} Produk</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Detail Produk --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-boxes me-2"></i> Detail Produk
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="printTable">
                        <thead class="table-primary text-center">
                            <tr>
                                <th width="4%">No</th>
                                <th width="18%">Produk</th>
                                <th width="12%">Barcode Batch</th>
                                <th width="10%">Supplier</th>
                                <th width="9%">Kategori</th>
                                <th width="7%">Satuan</th>
                                <th width="7%">Qty</th>
                                <th width="10%">Harga Beli</th>
                                <th width="11%">Kadaluwarsa</th>
                                <th width="12%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pembelian->details as $index => $d)
                                <tr class="table-row">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $d->produk->nama_produk ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $barcodeValue = $d->barcode_batch ?? ($d->produk->barcode ?? '-');
                                        @endphp

                                        @if ($barcodeValue && $barcodeValue != '-')
                                            <div class="d-flex flex-column align-items-center gap-1 py-1">
                                                <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($barcodeValue) }}&code=Code128&translate-esc=on&dpi=150&hidehrt=True"
                                                    alt="Barcode" style="height: 35px; width: auto;" class="barcode-img">
                                                <span class="font-monospace barcode-text">
                                                    {{ $barcodeValue }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $d->produk->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="text-center">{{ $d->produk->kategori->nama_kategori ?? '-' }}</td>
                                    <td class="text-center">{{ $d->produk->satuan->nama_satuan ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ $d->jumlah }}</td>
                                    <td class="text-end">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($d->kadaluwarsa)
                                            @php
                                                $kadaluwarsa = \Carbon\Carbon::parse($d->kadaluwarsa);
                                                $isExpired = $kadaluwarsa->isPast();
                                                $isExpiringSoon = $kadaluwarsa->diffInDays(now()) <= 30 && !$isExpired;
                                            @endphp
                                            <span
                                                class="badge {{ $isExpired ? 'bg-danger' : ($isExpiringSoon ? 'bg-warning text-dark' : 'bg-success') }}">
                                                {{ $kadaluwarsa->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Belum ada detail pembelian</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Card Total --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-end">
                    <div class="total-box p-3">
                        <div class="d-flex justify-content-between align-items-center" style="min-width: 300px;">
                            <h5 class="mb-0 text-dark fw-semibold">Total Pembelian:</h5>
                            <h4 class="mb-0 text-success fw-bold">
                                Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tanda Tangan (Print Only) --}}
        <div class="print-signature card shadow-sm border-0 mb-4">
            <div class="card-body p-5">
                <div class="row">
                    <div class="col-6 text-center">
                        <p class="mb-5 fw-semibold">Diterima Oleh,</p>
                        <div class="signature-line"></div>
                        <p class="mb-0 mt-2">(.......................)</p>
                    </div>
                    <div class="col-6 text-center">
                        <p class="mb-5 fw-semibold">Diserahkan Oleh,</p>
                        <div class="signature-line"></div>
                        <p class="mb-0 mt-2">({{ $pembelian->user->name ?? '.......................' }})</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-between align-items-center gap-2 mb-4 print-hide">
            <div>
                <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pembelian.pdf', $pembelian->pembelian_id) }}" class="btn btn-primary px-4"
                    target="_blank">
                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                </a>
            </div>
        </div>

    </div>

    {{-- Styles --}}
    <style>
        /* Card Styles */
        .card {
            border-radius: 0.75rem;
        }

        /* Info Box */
        .info-box {
            background: #f8f9fa;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
        }

        /* Total Box */
        .total-box {
            background: #e8f5e9;
            border-radius: 0.5rem;
            border: 2px solid #28a745;
        }

        /* Table Styles */
        .table {
            font-size: 0.9rem;
        }

        .table thead th {
            font-weight: 600;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* Barcode Styles */
        .barcode-img {
            max-width: 100%;
            height: auto;
        }

        .barcode-text {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .font-monospace {
            font-family: 'Courier New', Courier, monospace;
        }

        /* Print Signature - Hidden by default */
        .print-signature {
            display: none;
        }

        .signature-line {
            border-bottom: 2px solid #000;
            width: 200px;
            margin: 0 auto;
        }

        /* Print Styles */
        @media print {

            /* Hide elements */
            .print-hide {
                display: none !important;
            }

            /* Show signature */
            .print-signature {
                display: block !important;
                page-break-inside: avoid;
            }

            /* Reset backgrounds */
            body {
                background: white !important;
                margin: 0;
                padding: 0;
            }

            .container-fluid {
                padding: 0 !important;
                max-width: 100% !important;
            }

            /* Card adjustments */
            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
                margin-bottom: 0.5cm !important;
                page-break-inside: avoid;
            }

            .card-header {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                border-bottom: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .card-body {
                padding: 0.5cm !important;
            }

            /* Header */
            .print-header h3 {
                color: #000 !important;
            }

            /* Info box */
            .info-box {
                background: #f8f9fa !important;
                border: 1px solid #000;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Table */
            .table {
                font-size: 0.75rem !important;
                page-break-inside: auto;
            }

            .table thead th {
                background-color: #e9ecef !important;
                color: #000 !important;
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table tbody td {
                border: 1px solid #000 !important;
                color: #000 !important;
            }

            .table tbody tr {
                page-break-inside: avoid;
            }

            /* Barcode */
            .barcode-img {
                height: 30px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Badge */
            .badge {
                border: 1px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bg-success {
                background-color: #d4edda !important;
                color: #000 !important;
            }

            .bg-warning {
                background-color: #fff3cd !important;
                color: #000 !important;
            }

            .bg-danger {
                background-color: #f8d7da !important;
                color: #000 !important;
            }

            /* Total box */
            .total-box {
                background: #e8f5e9 !important;
                border: 2px solid #28a745 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .text-success {
                color: #28a745 !important;
            }

            /* Page breaks */
            h3,
            h5 {
                page-break-after: avoid;
            }
        }
    </style>
@endsection
