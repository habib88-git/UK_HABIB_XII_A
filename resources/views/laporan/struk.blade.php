<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Penjualan</title>
    <style>
        body { 
            font-family: monospace; 
            font-size: 12px; 
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .center { text-align: center; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        td, th { 
            padding: 3px 0; 
            vertical-align: top;
        }
        .line { 
            border-top: 1px dashed #000; 
            margin: 5px 0; 
        }
        .bold { font-weight: bold; }
        .product-table th {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .product-table td {
            padding: 2px 0;
        }
        .right { text-align: right; }
        .left { text-align: left; }
    </style>
</head>
<body>
    <div class="center">
        <h4>Toko Martkita</h4>
        <p>Jl. Kartika 3<br>Telp: 0812-3456-7890</p>
    </div>
    <div class="line"></div>

    <p>No Transaksi : {{ $penjualan->penjualan_id }}<br>
       Tanggal : {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->timezone('Asia/Jakarta')->format('d-m-Y H:i') }}<br>
       Kasir : {{ $penjualan->user->name ?? '-' }}<br>
       Pelanggan : {{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</p>

    <div class="line"></div>
    
    <!-- Tabel produk dengan kolom yang jelas -->
    <table class="product-table">
        <thead>
            <tr>
                <th class="left">Nama Produk</th>
                <th class="right">Jumlah</th>
                <th class="right">Harga</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->detailPenjualans as $d)
            <tr>
                <td class="left">{{ $d->produk->nama_produk }}</td>
                <td class="right">{{ $d->jumlah_produk }}</td>
                <td class="right">{{ number_format($d->produk->harga_jual,0,',','.') }}</td>
                <td class="right">{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="line"></div>

    <!-- Ringkasan pembayaran -->
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">Rp {{ number_format($penjualan->total_harga,0,',','.') }}</td>
        </tr>
        @if($penjualan->diskon > 0)
        <tr>
            <td>Diskon ({{ floor($penjualan->total_harga / 100000) * 5 }}%)</td>
            <td class="right">- Rp {{ number_format($penjualan->diskon,0,',','.') }}</td>
        </tr>
        @endif
        <tr class="bold">
            <td><strong>Total</strong></td>
            <td class="right"><strong>Rp {{ number_format($penjualan->total_harga - $penjualan->diskon,0,',','.') }}</strong></td>
        </tr>
        <tr>
            <td>Bayar ({{ ucfirst($penjualan->pembayaran->metode ?? '-') }})</td>
            <td class="right">Rp {{ number_format($penjualan->pembayaran->jumlah ?? 0,0,',','.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp {{ number_format($penjualan->pembayaran->kembalian ?? 0,0,',','.') }}</td>
        </tr>
    </table>
    <div class="line"></div>

    @if($penjualan->diskon > 0)
    <p class="center" style="font-size: 10px;">🎉 Anda hemat Rp {{ number_format($penjualan->diskon,0,',','.') }} 🎉</p>
    @endif

    <p class="center">Terima Kasih<br>--- Barang yang sudah dibeli tidak dapat dikembalikan ---</p>

    <p class="center" style="font-size: 10px; margin-top: 10px;">
        Dapatkan diskon 5% per Rp 100.000<br>
        Belanja lebih banyak, hemat lebih banyak!
    </p>
</body>
</html>