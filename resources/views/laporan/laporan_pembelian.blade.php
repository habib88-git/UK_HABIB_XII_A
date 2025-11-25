<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian #{{ str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            padding: 1cm;
        }

        .header {
            text-align: center;
            margin-bottom: 0.8cm;
            padding-bottom: 0.4cm;
            border-bottom: 3px solid #000;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 0.2cm;
            text-transform: uppercase;
        }

        .header p {
            font-size: 10pt;
            color: #333;
        }

        .invoice-info {
            margin-bottom: 0.6cm;
            text-align: center;
        }

        .invoice-info .invoice-number {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
        }

        .info-section {
            margin-bottom: 0.6cm;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 0.15cm 0.2cm;
            vertical-align: top;
        }

        .info-table .label {
            width: 25%;
            font-weight: bold;
        }

        .info-table .separator {
            width: 3%;
            text-align: center;
        }

        .info-table .value {
            width: 72%;
        }

        .info-row {
            background-color: #f8f9fa;
        }

        .info-row:nth-child(even) {
            background-color: #fff;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 0.5cm 0 0.3cm 0;
            padding-bottom: 0.1cm;
            border-bottom: 2px solid #333;
            text-transform: uppercase;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5cm;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #000;
            padding: 0.2cm;
            text-align: left;
        }

        .product-table th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }

        .product-table td {
            font-size: 10pt;
        }

        .product-table .text-center {
            text-align: center;
        }

        .product-table .text-right {
            text-align: right;
        }

        .product-table .text-end {
            text-align: right;
        }

        .product-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .barcode-cell {
            text-align: center;
            padding: 0.15cm;
        }

        .barcode-img {
            height: 28px;
            width: auto;
            display: block;
            margin: 0 auto 0.1cm auto;
        }

        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }

        .badge {
            display: inline-block;
            padding: 0.1cm 0.25cm;
            border-radius: 0.1cm;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .total-section {
            margin-top: 0.5cm;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            background-color: #e8f5e9;
            border: 2px solid #28a745;
            padding: 0.3cm 0.5cm;
            border-radius: 0.1cm;
            min-width: 8cm;
        }

        .total-box .total-label {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 0.1cm;
        }

        .total-box .total-value {
            font-size: 16pt;
            font-weight: bold;
            color: #28a745;
        }

        .signature-section {
            margin-top: 1.5cm;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table;
            width: 100%;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 1.2cm;
        }

        .signature-line {
            border-bottom: 2px solid #000;
            width: 5cm;
            margin: 0 auto 0.2cm auto;
        }

        .signature-name {
            font-weight: bold;
        }

        .footer {
            margin-top: 1cm;
            padding-top: 0.3cm;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }

        /* Untuk multi-page */
        @page {
            margin: 1cm;
        }

        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>Laporan Pembelian</h1>
        <p>Dokumen Transaksi Pembelian Barang</p>
    </div>

    {{-- INVOICE INFO --}}
    <div class="invoice-info">
        <div class="invoice-number">
            No. Invoice: #{{ str_pad($pembelian->pembelian_id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    {{-- INFORMASI PEMBELIAN --}}
    <div class="section-title">Informasi Pembelian</div>
    <div class="info-section">
        <table class="info-table">
            <tr class="info-row">
                <td class="label">Tanggal Pembelian</td>
                <td class="separator">:</td>
                <td class="value">
                    @php
                        $tanggal = \Carbon\Carbon::parse($pembelian->tanggal);
                        if ($tanggal->format('H:i:s') === '00:00:00') {
                            echo $tanggal->translatedFormat('l, d F Y');
                        } else {
                            echo $tanggal->translatedFormat('l, d F Y - H:i');
                        }
                    @endphp
                </td>
            </tr>
            <tr class="info-row">
                <td class="label">Admin/Petugas</td>
                <td class="separator">:</td>
                <td class="value">{{ $pembelian->user->name ?? '-' }}</td>
            </tr>
            <tr class="info-row">
                <td class="label">Total Item Produk</td>
                <td class="separator">:</td>
                <td class="value">{{ $pembelian->details->count() }} Produk</td>
            </tr>
        </table>
    </div>

    {{-- DETAIL PRODUK --}}
    <div class="section-title">Detail Produk</div>
    <table class="product-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 20%;">Nama Produk</th>
                <th style="width: 13%;">Barcode Batch</th>
                <th style="width: 10%;">Supplier</th>
                <th style="width: 9%;">Kategori</th>
                <th style="width: 7%;">Satuan</th>
                <th style="width: 6%;">Qty</th>
                <th style="width: 11%;">Harga Beli</th>
                <th style="width: 10%;">Kadaluwarsa</th>
                <th style="width: 11%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembelian->details as $index => $d)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                    <td class="barcode-cell">
                        @php
                            $barcodeValue = $d->barcode_batch ?? ($d->produk->barcode ?? '-');
                        @endphp

                        @if ($barcodeValue && $barcodeValue != '-')
                            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($barcodeValue) }}&code=Code128&translate-esc=on&dpi=150&hidehrt=True"
                                alt="Barcode"
                                class="barcode-img">
                            <div class="barcode-text">{{ $barcodeValue }}</div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $d->produk->supplier->nama_supplier ?? '-' }}</td>
                    <td class="text-center">{{ $d->produk->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">{{ $d->produk->satuan->nama_satuan ?? '-' }}</td>
                    <td class="text-center"><strong>{{ $d->jumlah }}</strong></td>
                    <td class="text-right">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($d->kadaluwarsa)
                            @php
                                $kadaluwarsa = \Carbon\Carbon::parse($d->kadaluwarsa);
                                $isExpired = $kadaluwarsa->isPast();
                                $isExpiringSoon = $kadaluwarsa->diffInDays(now()) <= 30 && !$isExpired;
                            @endphp
                            <span class="badge {{ $isExpired ? 'badge-danger' : ($isExpiringSoon ? 'badge-warning' : 'badge-success') }}">
                                {{ $kadaluwarsa->format('d/m/Y') }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right"><strong>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data produk</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="total-section no-break">
        <div class="total-box">
            <div class="total-label">TOTAL PEMBELIAN</div>
            <div class="total-value">Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y - H:i:s') }}</p>
        <p>Dokumen ini sah dan diproses secara elektronik</p>
    </div>
</body>
</html>
