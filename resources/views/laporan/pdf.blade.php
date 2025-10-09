<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #e8f5e9; font-weight: bold; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Laporan Penjualan</h2>
    <p>Tanggal Cetak: {{ now()->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total Harga</th>
                <th>Metode</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSubtotal = 0;
                $totalDiskon = 0;
                $totalAkhir = 0;
            @endphp

            @foreach($penjualans as $p)
            @php
                $totalSubtotal += $p->total_harga;
                $totalDiskon += $p->diskon ?? 0;
                $totalAkhir += ($p->total_harga - ($p->diskon ?? 0));
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}</td>
                <td>{{ $p->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                <td class="text-right">Rp {{ number_format($p->total_harga,0,',','.') }}</td>
                <td class="text-right">
                    @if(($p->diskon ?? 0) > 0)
                        - Rp {{ number_format($p->diskon,0,',','.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($p->total_harga - ($p->diskon ?? 0),0,',','.') }}</td>
                <td>{{ ucfirst($p->pembayaran->metode ?? '-') }}</td>
                <td>{{ $p->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalSubtotal,0,',','.') }}</strong></td>
                <td class="text-right"><strong>- Rp {{ number_format($totalDiskon,0,',','.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalAkhir,0,',','.') }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <h4>Ringkasan:</h4>
        <table style="width: 50%; border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 3px;">Total Transaksi</td>
                <td style="border: none; padding: 3px;">: {{ count($penjualans) }} transaksi</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 3px;">Total Subtotal</td>
                <td style="border: none; padding: 3px;">: Rp {{ number_format($totalSubtotal,0,',','.') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 3px;">Total Diskon</td>
                <td style="border: none; padding: 3px;">: Rp {{ number_format($totalDiskon,0,',','.') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 3px;"><strong>Total Penjualan</strong></td>
                <td style="border: none; padding: 3px;"><strong>: Rp {{ number_format($totalAkhir,0,',','.') }}</strong></td>
            </tr>
        </table>
    </div>

    <p style="margin-top: 30px; font-size: 10px; color: #666;">
        <em>* Diskon otomatis: 5% per kelipatan Rp 100.000</em>
    </p>
</body>
</html>
