<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Struk Penjualan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 58mm auto;
            margin: 0;
        }

        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            width: 58mm;
            max-width: 58mm;
            margin: 0 auto;
            padding: 5px;
            background: white;
        }

        .center {
            text-align: center;
        }

        h4 {
            margin: 3px 0;
            font-size: 12px;
            font-weight: bold;
        }

        p {
            margin: 2px 0;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 2px 0;
            vertical-align: top;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .product-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-size: 8px;
        }

        .product-table td {
            padding: 3px 0;
            font-size: 9px;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .total-section {
            margin-top: 3px;
        }

        .total-section td {
            padding: 1px 0;
            font-size: 9px;
        }

        .total-row {
            font-weight: bold;
            font-size: 10px;
            padding-top: 3px !important;
        }

        .info-table td:first-child {
            width: 65px;
        }

        .promo-text {
            font-size: 8px;
            margin: 5px 0;
            line-height: 1.4;
        }

        .diskon-detail {
            font-size: 7px;
            color: #666;
            padding-left: 8px;
            font-style: italic;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="center">
        <h4>TOKO MARTKITA</h4>
        <p style="font-size: 8px;">Jl. Kartika 3<br>Telp: 0812-3456-7890</p>
    </div>
    <div class="line"></div>

    <table class="info-table" style="margin-bottom: 3px; font-size: 8px;">
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

    <table class="product-table">
        <thead>
            <tr>
                <th class="left" style="width: 35%;">Produk</th>
                <th class="right" style="width: 13%;">Qty</th>
                <th class="right" style="width: 26%;">Harga</th>
                <th class="right" style="width: 26%;">Total</th>
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

    @php
        // 🔥 HITUNG ULANG DISKON DARI DETAIL PENJUALAN
        $subtotalAsli = 0; // Total jika tidak ada diskon
        $subtotalSetelahDiskon = 0; // Total setelah diskon expiry
        
        foreach ($penjualan->detailPenjualans as $d) {
            $subtotalAsli += $d->produk->harga_jual * $d->jumlah_produk;
            $subtotalSetelahDiskon += $d->subtotal;
        }
        
        // Diskon expiry = selisih harga asli dengan harga setelah diskon
        $diskonExpiry = $subtotalAsli - $subtotalSetelahDiskon;
        
        // Diskon member = total diskon - diskon expiry
        $diskonMember = $penjualan->diskon - $diskonExpiry;
        
        // Hitung total item untuk cek syarat diskon member
        $totalItem = $penjualan->detailPenjualans->sum('jumlah_produk');
    @endphp

    <table class="total-section">
        <tr>
            <td style="width: 50%;">Subtotal</td>
            <td class="right">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
        </tr>
        
        {{-- 🔥 DISKON PRODUK (EXPIRY) --}}
        @if ($diskonExpiry > 0)
            <tr>
                <td>Diskon Produk</td>
                <td class="right">- Rp {{ number_format($diskonExpiry, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="diskon-detail">
                    (Produk mendekati kadaluwarsa)
                </td>
            </tr>
        @endif
        
        {{-- 🔥 DISKON MEMBER --}}
        @if ($diskonMember > 0 && $penjualan->pelanggan_id)
            <tr>
                <td>Diskon Member</td>
                <td class="right">- Rp {{ number_format($diskonMember, 0, ',', '.') }}</td>
            </tr>
            @php
                $alasanDiskon = [];
                if ($totalItem >= 10) $alasanDiskon[] = "Beli {$totalItem} item";
                if ($penjualan->total_harga >= 100000) $alasanDiskon[] = "Belanja ≥Rp100k";
            @endphp
            @if(count($alasanDiskon) > 0)
            <tr>
                <td colspan="2" class="diskon-detail">
                    ({{ implode(' + ', $alasanDiskon) }})
                </td>
            </tr>
            @endif
        @endif
        
        {{-- 🔥 TOTAL SEMUA DISKON (jika ada lebih dari 1 jenis) --}}
        @if ($penjualan->diskon > 0 && ($diskonExpiry > 0 && $diskonMember > 0))
            <tr style="border-top: 1px dashed #ccc; padding-top: 2px;">
                <td><strong>Total Diskon</strong></td>
                <td class="right"><strong>- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</strong></td>
            </tr>
        @endif
        
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
        <p class="center promo-text" style="margin: 5px 0; font-weight: bold;">
            🎉 Anda hemat Rp {{ number_format($penjualan->diskon, 0, ',', '.') }} 🎉
        </p>
        <div class="line"></div>
    @endif

    <p class="center" style="margin: 5px 0; font-size: 9px;">Terima Kasih Atas Kunjungan Anda</p>
    <p class="center" style="font-size: 8px; margin: 3px 0;">Barang yang sudah dibeli<br>tidak dapat dikembalikan</p>

   

    <script>
        window.onload = function() {
            // Auto print saat halaman dibuka
            window.print();

            // Tutup window atau redirect setelah print
            window.onafterprint = function() {
                window.close(); // Tutup tab
            }
        }
    </script>
</body>

</html>