@extends('layout.master')

@section('title', 'Detail Pembelian')

@section('content')
<div class="container-fluid">
    <h2>Detail Pembelian</h2>

    <div class="mb-3">
        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pembelian->tanggal)->translatedFormat('d F Y') }} <br>
        <strong>Supplier:</strong> {{ $pembelian->supplier->nama_supplier ?? '-' }} <br>
        <strong>User:</strong> {{ $pembelian->user->name ?? '-' }} <br>
        <strong>Total Harga:</strong> Rp {{ number_format($pembelian->total_harga, 2) }}
    </div>

    <h5>Detail Produk</h5>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Harga Beli</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($pembelian->details as $d)
            <tr>
                <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>{{ number_format($d->harga_beli, 2) }}</td>
                <td>{{ number_format($d->subtotal, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada detail pembelian</td>
            </tr>
        @endforelse
    </tbody>
</table>

    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
