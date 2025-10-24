<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            max-width: 300px;
            margin: 0;
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        h4 {
            margin: 5px 0;
            font-size: 14px;
        }

        p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 3px 0;
            vertical-align: top;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .product-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            font-size: 10px;
        }
        
        .product-table td {
            padding: 4px 0;
            font-size: 11px;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .total-section {
            margin-top: 5px;
        }

        .total-section td {
            padding: 2px 0;
        }

        .total-row {
            font-weight: bold;
            font-size: 12px;
            padding-top: 5px !important;
        }
    </style>
</head>

<body>
    <div class="center">
        <h4>TOKO MARTKITA</h4>
        <p>Jl. Kartika 3<br>Telp: 0812-3456-7890</p>
    </div>
    <div class="line"></div>

    <table style="margin-bottom: 5px;">
        <tr>
            <td>Tanggal</td>
            <td>:
                {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}
            </td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $penjualan->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>: {{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
        </tr>
    </table>

    <!-- Hapus line ini karena sudah ada border-top di th -->
    
    <table class="product-table">
        <thead>
            <tr>
                <th class="left" style="width: 40%;">Produk</th>
                <th class="right" style="width: 15%;">Qty</th>
                <th class="right" style="width: 22%;">Harga</th>
                <th class="right" style="width: 23%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan->detailPenjualans as $d)
                <tr>
                    <td class="left">{{ $d->produk->nama_produk }}</td>
                    <td class="right">{{ $d->jumlah_produk }}</td>
                    <td class="right">{{ number_format($d->produk->harga_jual, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table class="total-section">
        <tr>
            <td style="width: 50%;">Subtotal</td>
            <td class="right">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
        </tr>
        @if ($penjualan->diskon > 0)
            <tr>
                <td>Diskon ({{ floor($penjualan->total_harga / 100000) * 5 }}%)</td>
                <td class="right">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td><strong>TOTAL</strong></td>
            <td class="right"><strong>Rp
                    {{ number_format($penjualan->total_harga - $penjualan->diskon, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Bayar ({{ ucfirst($penjualan->pembayaran->metode ?? '-') }})</td>
            <td class="right">Rp {{ number_format($penjualan->pembayaran->jumlah ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp {{ number_format($penjualan->pembayaran->kembalian ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    @if ($penjualan->diskon > 0)
        <p class="center" style="font-size: 10px; margin: 8px 0;">🎉 Anda hemat Rp
            {{ number_format($penjualan->diskon, 0, ',', '.') }} 🎉</p>
        <div class="line"></div>
    @endif

    <p class="center" style="margin: 8px 0;">Terima Kasih Atas Kunjungan Anda</p>
    <p class="center" style="font-size: 9px; margin: 5px 0;">Barang yang sudah dibeli<br>tidak dapat dikembalikan</p>

    <div class="line"></div>

    <p class="center" style="font-size: 9px; margin: 5px 0;">
        💰 Promo Diskon Belanja! 💰<br>
        Diskon 5% setiap kelipatan Rp 100.000<br>
        Belanja lebih banyak, hemat lebih banyak!
    </p>
</body>

</html>