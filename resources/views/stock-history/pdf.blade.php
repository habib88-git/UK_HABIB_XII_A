<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan History Stock</title>
    <style>
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
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .filter-info {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .filter-info table {
            width: 100%;
        }
        .filter-info td {
            padding: 3px 8px;
            font-size: 10px;
        }
        .filter-info strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4e73df;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #3a5dc7;
        }
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 10px;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success {
            background-color: #1cc88a;
            color: white;
        }
        .badge-danger {
            background-color: #e74a3b;
            color: white;
        }
        .badge-primary {
            background-color: #4e73df;
            color: white;
        }
        .badge-secondary {
            background-color: #858796;
            color: white;
        }
        .badge-light {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }
        .text-success {
            color: #1cc88a;
            font-weight: bold;
        }
        .text-danger {
            color: #e74a3b;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .summary {
            margin-top: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-around;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>📊 LAPORAN HISTORY STOCK PRODUK</h1>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }} WIB</p>
    </div>

    {{-- Filter Info --}}
    <div class="filter-info">
        <table>
            <tr>
                <td width="15%"><strong>Produk:</strong></td>
                <td width="35%">{{ $filterInfo['produk'] }}</td>
                <td width="15%"><strong>Tipe:</strong></td>
                <td width="35%">{{ $filterInfo['tipe'] }}</td>
            </tr>
            <tr>
                <td><strong>Kategori:</strong></td>
                <td>{{ $filterInfo['kategori'] }}</td>
                <td><strong>Periode:</strong></td>
                <td>{{ $filterInfo['tanggal_dari'] }} s/d {{ $filterInfo['tanggal_sampai'] }}</td>
            </tr>
        </table>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-item">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($histories->count()) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Stok Masuk</div>
            <div class="value text-success">+{{ number_format($histories->where('tipe', 'masuk')->sum('jumlah')) }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Stok Keluar</div>
            <div class="value text-danger">-{{ number_format($histories->where('tipe', 'keluar')->sum('jumlah')) }}</div>
        </div>
    </div>

    {{-- Table Data --}}
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Produk</th>
                <th width="10%">Barcode</th>
                <th width="8%">Kategori</th>
                <th width="7%">Tipe</th>
                <th width="6%">Jumlah</th>
                <th width="7%">Stok Awal</th>
                <th width="7%">Stok Akhir</th>
                <th width="17%">Keterangan</th>
                <th width="10%">User</th>
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
                            <span class="badge badge-success">↓ Masuk</span>
                        @else
                            <span class="badge badge-danger">↑ Keluar</span>
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
                    <td style="font-size: 9px;">
                        {{ $history->keterangan ?? '-' }}
                        @if($history->referensi_tipe && $history->referensi_id)
                            <br><span class="badge badge-light">{{ ucfirst($history->referensi_tipe) }} #{{ $history->referensi_id }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $history->user->name ?? 'System' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px; color: #999;">
                        Tidak ada data history stock
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>Generated by Inventory Management System | Total Records: {{ number_format($histories->count()) }}</p>
    </div>
</body>
</html>