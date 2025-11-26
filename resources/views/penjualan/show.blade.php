@extends('layout.master')
@section('title', 'Detail Penjualan')
@section('content')
    <div class="container-fluid" id="container-wrapper">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h4 class="m-0 font-weight-bold text-primary">🧾 Detail Penjualan</h4>
            </div>
            <div class="card-body">
                {{-- Info Umum --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong>
                            {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y H:i') }}</p>
                        <p><strong>Pelanggan:</strong> {{ $penjualan->pelanggan->nama_pelanggan ?? 'Umum' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Kasir:</strong> {{ $penjualan->user->name ?? '-' }}</p>
                        <p><strong>Metode Bayar:</strong> <span class="badge bg-info">{{ ucfirst($penjualan->pembayaran->metode ?? '-') }}</span></p>
                    </div>
                </div>

                {{-- Produk Dibeli --}}
                <h5 class="text-secondary mb-3">📦 Produk Dibeli</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Produk</th>
                                <th width="100px">Jumlah</th>
                                <th width="150px">Harga Satuan</th>
                                <th width="150px">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subtotalAsli = 0;
                                $subtotalSetelahDiskon = 0;
                            @endphp
                            @foreach ($penjualan->detailPenjualans as $detail)
                                @php
                                    $hargaAsli = $detail->produk->harga_jual * $detail->jumlah_produk;
                                    $subtotalAsli += $hargaAsli;
                                    $subtotalSetelahDiskon += $detail->subtotal;
                                    $adaDiskonItem = $hargaAsli > $detail->subtotal;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $detail->produk->nama_produk }}
                                        @if($adaDiskonItem)
                                            <span class="badge bg-danger ms-2">Diskon Expiry</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $detail->jumlah_produk }}</td>
                                    <td class="text-end">
                                        @if($adaDiskonItem)
                                            <s class="text-muted">Rp {{ number_format($detail->produk->harga_jual, 0, ',', '.') }}</s>
                                            <br>
                                            <span class="text-success">Rp {{ number_format($detail->subtotal / $detail->jumlah_produk, 0, ',', '.') }}</span>
                                        @else
                                            Rp {{ number_format($detail->produk->harga_jual, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($adaDiskonItem)
                                            <s class="text-muted">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</s>
                                            <br>
                                            <strong class="text-success">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                                        @else
                                            <strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                // Hitung diskon expiry dan member
                                $diskonExpiry = $subtotalAsli - $subtotalSetelahDiskon;
                                $diskonMember = $penjualan->diskon - $diskonExpiry;
                                $totalItem = $penjualan->detailPenjualans->sum('jumlah_produk');
                            @endphp
                            
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                            
                            {{-- DISKON EXPIRY --}}
                            @if ($diskonExpiry > 0)
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <strong>Diskon Produk</strong>
                                        <br>
                                        <small class="text-muted">(Produk mendekati kadaluwarsa)</small>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong>- Rp {{ number_format($diskonExpiry, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif
                            
                            {{-- DISKON MEMBER --}}
                            @if ($diskonMember > 0 && $penjualan->pelanggan_id)
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <strong>Diskon Member (5%)</strong>
                                        <br>
                                        <small class="text-muted">
                                            @php
                                                $alasanDiskon = [];
                                                if ($totalItem >= 10) $alasanDiskon[] = "Beli {$totalItem} item";
                                                if ($penjualan->total_harga >= 100000) $alasanDiskon[] = "Belanja ≥ Rp100k";
                                            @endphp
                                            @if(count($alasanDiskon) > 0)
                                                ({{ implode(' + ', $alasanDiskon) }})
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong>- Rp {{ number_format($diskonMember, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif
                            
                            {{-- TOTAL SEMUA DISKON (jika ada lebih dari 1 jenis) --}}
                            @if ($penjualan->diskon > 0 && ($diskonExpiry > 0 && $diskonMember > 0))
                                <tr style="border-top: 2px solid #dee2e6;">
                                    <td colspan="3" class="text-end">
                                        <strong>Total Semua Diskon:</strong>
                                    </td>
                                    <td class="text-end text-danger">
                                        <strong>- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endif
                            
                            <tr class="table-success" style="border-top: 3px solid #28a745;">
                                <td colspan="3" class="text-end"><strong>TOTAL BAYAR:</strong></td>
                                <td class="text-end">
                                    <strong style="font-size: 1.1em;">Rp {{ number_format($penjualan->total_harga - $penjualan->diskon, 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Ringkasan Pembayaran --}}
                <h5 class="text-secondary mt-4 mb-3">💳 Pembayaran</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Metode:</strong>
                            <p class="mb-0">
                                <span class="badge bg-info" style="font-size: 1em;">
                                    {{ ucfirst($penjualan->pembayaran->metode ?? '-') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Jumlah Bayar:</strong>
                            <p class="mb-0 text-success" style="font-size: 1.1em; font-weight: bold;">
                                Rp {{ number_format($penjualan->pembayaran->jumlah ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0 shadow-sm p-3">
                            <strong>Kembalian:</strong>
                            <p class="mb-0 text-danger" style="font-size: 1.1em; font-weight: bold;">
                                Rp {{ number_format($penjualan->pembayaran->kembalian ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Info Hemat (jika ada diskon) --}}
                @if ($penjualan->diskon > 0)
                    <div class="alert alert-success mt-3" role="alert">
                        <h5 class="alert-heading">🎉 Penghematan</h5>
                        <p class="mb-0">
                            Pelanggan hemat <strong>Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</strong> 
                            dari transaksi ini!
                        </p>
                    </div>
                @endif

                {{-- Tombol --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Penjualan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection