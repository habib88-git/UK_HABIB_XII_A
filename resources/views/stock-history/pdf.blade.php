<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan History Stock</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            padding: 20px;
            color: #333;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        /* Filter Info */
        .filter-info {
            background: #f8f9fa;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .filter-info table {
            width: 100%;
        }

        .filter-info td {
            padding: 4px 10px;
            font-size: 10px;
            vertical-align: top;
        }

        .filter-info td:first-child {
            width: 15%;
            font-weight: bold;
        }

        /* Summary - Layout Horizontal dengan Kolom Terpisah */
        .summary {
            margin-top: 15px;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-col {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }

        .summary-item {
            display: block;
        }

        .summary-item .label {
            font-size: 10px;
            color: #666;
            font-weight: bold;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .summary-item .value {
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .text-success { color: #1cc88a; }
        .text-danger  { color: #e74a3b; }
        .text-primary { color: #4e73df; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th {
            background-color: #4e73df;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #3a5dc7;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 10px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Alignment Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Badges */
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }

        .badge-success { background-color: #1cc88a; color: #fff; }
        .badge-danger  { background-color: #e74a3b; color: #fff; }
        .badge-primary { background-color: #4e73df; color: #fff; }
        .badge-secondary { background-color: #858796; color: #fff; }

        /* Misc */
        .keterangan-simple {
            font-size: 9px;
            line-height: 1.3;
        }

        .no-data {
            padding: 20px;
            color: #999;
            text-align: center;
            font-style: italic;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        /* Column Widths */
        .col-no { width: 3%; }
        .col-tanggal { width: 10%; }
        .col-produk { width: 15%; }
        .col-barcode { width: 10%; }
        .col-kategori { width: 8%; }
        .col-tipe { width: 7%; }
        .col-jumlah { width: 6%; }
        .col-stok-awal { width: 7%; }
        .col-stok-akhir { width: 7%; }
        .col-keterangan { width: 17%; }
        .col-user { width: 10%; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>LAPORAN HISTORY STOCK PRODUK</h1>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }} WIB</p>
    </div>

    <!-- Filter Info -->
    <div class="filter-info">
        <table>
            <tr>
                <td><strong>Produk:</strong></td>
                <td>{{ $filterInfo['produk'] }}</td>
                <td><strong>Tipe:</strong></td>
                <td>{{ $filterInfo['tipe'] }}</td>
            </tr>
            <tr>
                <td><strong>Kategori:</strong></td>
                <td>{{ $filterInfo['kategori'] }}</td>
                <td><strong>Periode:</strong></td>
                <td>{{ $filterInfo['tanggal_dari'] }} s/d {{ $filterInfo['tanggal_sampai'] }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary Horizontal dengan Kolom Terpisah -->
    <div class="summary">
        <div class="summary-row">
            <div class="summary-col">
                <div class="summary-item">
                    <div class="label">TOTAL TRANSAKSI</div>
                    <div class="value text-primary">5</div>
                </div>
            </div>
            <div class="summary-col">
                <div class="summary-item">
                    <div class="label">STOK MASUK</div>
                    <div class="value text-success">+500</div>
                </div>
            </div>
            <div class="summary-col">
                <div class="summary-item">
                    <div class="label">STOK KELUAR</div>
                    <div class="value text-danger">-0</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Data -->
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-produk">Produk</th>
                <th class="col-barcode">Barcode</th>
                <th class="col-kategori">Kategori</th>
                <th class="col-tipe">Tipe</th>
                <th class="col-jumlah">Jumlah</th>
                <th class="col-stok-awal">Stok Awal</th>
                <th class="col-stok-akhir">Stok Akhir</th>
                <th class="col-keterangan">Keterangan</th>
                <th class="col-user">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $history)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($history->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    </td>
                    <td>{{ $history->produk->nama_produk ?? '-' }}</td>
                    <td class="text-center">{{ $history->produk->barcode ?? '-' }}</td>
                    <td class="text-center">{{ $history->produk->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">
                        @if($history->tipe == 'masuk')
                            <span class="badge badge-success">Masuk</span>
                        @else
                            <span class="badge badge-danger">Keluar</span>
                        @endif
                    </td>
                    <td class="text-center {{ $history->tipe == 'masuk' ? 'text-success' : 'text-danger' }}">
                        {{ $history->tipe == 'masuk' ? '+' : '-' }}{{ number_format($history->jumlah) }}
                    </td>
                    <td class="text-center">{{ number_format($history->stok_sebelum) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $history->stok_sesudah > 0 ? 'primary' : 'secondary' }}">
                            {{ number_format($history->stok_sesudah) }}
                        </span>
                    </td>
                    <td class="keterangan-simple">{{ $history->keterangan ?? '-' }}</td>
                    <td class="text-center">{{ $history->user->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="no-data">Tidak ada data history stock</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Generated by Inventory Management System | Total Records: {{ number_format($histories->count()) }}</p>
    </div>

</body>
</html>
