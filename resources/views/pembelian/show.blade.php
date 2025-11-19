@extends('layout.master')
@section('title', 'Detail Pembelian')

@section('content')
    <div class="container-fluid py-4" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                {{-- Header --}}
                <div class="print-header mb-4 pb-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="fw-bold text-primary mb-1">
                                <i class="fas fa-file-invoice-dollar me-2"></i> Detail Pembelian
                            </h4>
                            <small class="text-muted">No. Invoice: #{{ str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) }}</small>
                        </div>
                        <div class="col-4 text-end print-hide">
                            <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-print me-1"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pembelian --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="40%" class="text-muted"><i class="fas fa-calendar-day me-2 text-primary"></i>Tanggal</td>
                                    <td width="5%">:</td>
                                    <td class="fw-semibold">
                                        @php
                                            $tanggal = \Carbon\Carbon::parse($pembelian->tanggal);
                                            if ($tanggal->format('H:i:s') === '00:00:00') {
                                                echo $tanggal->translatedFormat('d F Y');
                                            } else {
                                                echo $tanggal->translatedFormat('d F Y H:i');
                                            }
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-user me-2 text-success"></i>Admin</td>
                                    <td>:</td>
                                    <td class="fw-semibold">{{ $pembelian->user->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="40%" class="text-muted"><i class="fas fa-truck me-2 text-info"></i>Supplier</td>
                                    <td width="5%">:</td>
                                    <td class="fw-semibold">{{ $pembelian->supplier->nama_supplier ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-boxes me-2 text-warning"></i>Total Item</td>
                                    <td>:</td>
                                    <td class="fw-semibold">{{ $pembelian->details->count() }} Produk</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Detail Produk --}}
                <div class="mb-4">
                    <h5 class="text-primary mb-3 fw-semibold">
                        <i class="fas fa-boxes me-2"></i> Detail Produk
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="printTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="4%">No</th>
                                    <th width="18%">Produk</th>
                                    <th class="text-center" width="13%">Barcode Batch</th>
                                    <th class="text-center" width="10%">Supplier</th>
                                    <th class="text-center" width="9%">Kategori</th>
                                    <th class="text-center" width="7%">Satuan</th>
                                    <th class="text-center" width="7%">Qty</th>
                                    <th class="text-end" width="10%">Harga Beli</th>
                                    <th class="text-center" width="10%">Kadaluwarsa</th>
                                    <th class="text-end" width="12%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pembelian->details as $index => $d)
                                    <tr class="table-row">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $d->produk->nama_produk ?? '-' }}</div>
                                            @if($d->produk->kode_produk)
                                                <small class="text-muted">Kode: {{ $d->produk->kode_produk }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $barcodeValue = $d->barcode_batch ?? ($d->produk->barcode ?? '-');
                                            @endphp

                                            @if ($barcodeValue && $barcodeValue != '-')
                                                <div class="d-flex flex-column align-items-center gap-1 py-1">
                                                    <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($barcodeValue) }}&code=Code128&translate-esc=on&dpi=150&hidehrt=True"
                                                        alt="Barcode"
                                                        style="height: 35px; width: auto; display: block;"
                                                        class="barcode-img">
                                                    <span class="font-monospace" style="font-size: 0.7rem; letter-spacing: 0.5px;">
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
                                                <span class="badge {{ $isExpired ? 'bg-danger' : ($isExpiringSoon ? 'bg-warning text-dark' : 'bg-success') }}">
                                                    {{ $kadaluwarsa->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-lg me-2"></i> Belum ada detail pembelian
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Total --}}
                <div class="d-flex justify-content-end mb-4">
                    <div class="total-box">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-end pe-3"><strong>Total Pembelian:</strong></td>
                                <td class="text-end">
                                    <h4 class="mb-0 text-success fw-bold">
                                        Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}
                                    </h4>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Tanda Tangan (Print Only) --}}
                <div class="print-signature mt-5 pt-4">
                    <div class="row">
                        <div class="col-6 text-center">
                            <p class="mb-5">Diterima Oleh,</p>
                            <div class="signature-line"></div>
                            <p class="mb-0 mt-2">(.......................)</p>
                        </div>
                        <div class="col-6 text-center">
                            <p class="mb-5">Diserahkan Oleh,</p>
                            <div class="signature-line"></div>
                            <p class="mb-0 mt-2">({{ $pembelian->user->name ?? '.......................' }})</p>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-start flex-wrap tombol-aksi mt-5 pt-3 border-top print-hide">
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
        /* General Styles */
        .card {
            border-radius: 0.6rem;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .total-box {
            background: #e8f5e9;
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
            border: 2px solid #28a745;
        }

        /* Table Styles */
        .table {
            font-size: 0.9rem;
        }

        .table thead th {
            font-weight: 600;
            vertical-align: middle;
            background-color: #f1f3f5 !important;
            border: 1px solid #dee2e6;
            padding: 0.75rem 0.5rem;
        }

        .table tbody td {
            vertical-align: middle;
            border: 1px solid #dee2e6;
            padding: 0.6rem 0.5rem;
        }

        .table-row:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        /* Barcode Styles */
        .font-monospace {
            font-family: 'Courier New', Courier, monospace;
        }

        .barcode-img {
            max-width: 100%;
            height: auto;
        }

        /* Button Styles */
        .tombol-aksi {
            gap: 1rem;
        }

        .tombol-aksi .btn {
            margin-bottom: 0.5rem;
        }

        /* Print Signature */
        .print-signature {
            display: none;
        }

        .signature-line {
            border-bottom: 1px solid #000;
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
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
            }

            .card-body {
                padding: 1cm !important;
            }

            /* Header styles */
            .print-header {
                margin-bottom: 1cm !important;
            }

            .print-header h4 {
                font-size: 1.3rem !important;
                color: #000 !important;
            }

            /* Info box */
            .info-box {
                background: transparent !important;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            /* Table styles */
            .table {
                font-size: 0.75rem !important;
                page-break-inside: auto;
            }

            .table thead {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table thead th {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                border: 1px solid #000 !important;
                padding: 0.4rem 0.3rem !important;
                font-size: 0.7rem !important;
            }

            .table tbody td {
                border: 1px solid #000 !important;
                padding: 0.4rem 0.3rem !important;
                color: #000 !important;
            }

            .table tbody tr {
                page-break-inside: avoid;
            }

            .table-row:hover {
                background-color: transparent !important;
            }

            /* Barcode */
            .barcode-img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                height: 30px !important;
            }

            /* Badge colors */
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
                background: #f0f0f0 !important;
                border: 2px solid #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid;
            }

            .text-success {
                color: #000 !important;
            }

            /* Remove hover effects */
            * {
                transition: none !important;
            }

            /* Signature */
            .signature-line {
                border-bottom: 1px solid #000;
            }

            /* Page breaks */
            hr {
                page-break-after: avoid;
            }

            h5 {
                page-break-after: avoid;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table {
                font-size: 0.8rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.4rem 0.3rem;
            }

            .barcode-img {
                height: 30px !important;
            }
        }
    </style>
@endsection
