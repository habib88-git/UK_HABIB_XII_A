@extends('layout.master')
@section('title', 'History Stock')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history"></i> History Stock Produk
            </h1>
            <div>
                <a href="{{ route('stock-history.pdf', request()->query()) }}" class="btn btn-danger shadow-sm mr-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i> Filter Data
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('stock-history.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="produk_id" class="form-label">Produk</label>
                            <select name="produk_id" id="produk_id" class="form-control">
                                <option value="">-- Semua Produk --</option>
                                @foreach ($produks as $p)
                                    <option value="{{ $p->produk_id }}" {{ request('produk_id') == $p->produk_id ? 'selected' : '' }}>
                                        {{ $p->nama_produk }} ({{ $p->barcode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="tipe" class="form-label">Tipe Transaksi</label>
                            <select name="tipe" id="tipe" class="form-control">
                                <option value="">-- Semua --</option>
                                <option value="masuk" {{ request('tipe') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                                <option value="keluar" {{ request('tipe') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select name="kategori_id" id="kategori_id" class="form-control">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->kategori_id }}" {{ request('kategori_id') == $k->kategori_id ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="tanggal_dari" class="form-label">Dari Tanggal</label>
                            <input type="date" name="tanggal_dari" id="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="tanggal_sampai" class="form-label">Sampai Tanggal</label>
                            <input type="date" name="tanggal_sampai" id="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                        </div>

                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    @if(request()->anyFilled(['produk_id', 'tipe', 'kategori_id', 'tanggal_dari', 'tanggal_sampai']))
                        <a href="{{ route('stock-history.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i> Reset Filter
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Data History Stock</h6>
                        <span class="badge badge-info">{{ $histories->total() }} Transaksi</span>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-bordered align-items-center table-hover">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="11%">Tanggal</th>
                                    <th width="13%">Produk</th>
                                    <th width="10%">Barcode</th>
                                    <th width="7%">Kategori</th>
                                    <th width="7%">Tipe</th>
                                    <th width="7%">Jumlah</th>
                                    <th width="7%">Stok Awal</th>
                                    <th width="7%">Stok Akhir</th>
                                    <th width="15%">Keterangan</th>
                                    <th width="8%">User</th>
                                    <th width="7%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                    <tr>
                                        <td class="text-center">{{ $histories->firstItem() + $loop->index }}</td>
                                        <td class="text-center">
                                            <div class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($history->created_at)->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                                            </div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($history->created_at)->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                {{ $history->produk->nama_produk ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="barcode-visual">
                                                @if($history->produk && $history->produk->barcode)
                                                    @php
                                                        try {
                                                            echo DNS1D::getBarcodeHTML($history->produk->barcode, 'C128', 1.2, 30);
                                                        } catch (\Exception $e) {
                                                            echo '<small class="text-muted">Barcode Error</small>';
                                                        }
                                                    @endphp
                                                    <div class="mt-1">
                                                        <code>{{ $history->produk->barcode }}</code>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $history->produk->kategori->nama_kategori ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            @if($history->tipe == 'masuk')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-arrow-down"></i> Masuk
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-arrow-up"></i> Keluar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center font-weight-bold {{ $history->tipe == 'masuk' ? 'text-success' : 'text-danger' }}">
                                            {{ $history->tipe == 'masuk' ? '+' : '-' }}{{ number_format($history->jumlah) }}
                                        </td>
                                        <td class="text-center">{{ number_format($history->stok_sebelum) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $history->stok_sesudah > 0 ? 'primary' : 'secondary' }}">
                                                {{ number_format($history->stok_sesudah) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <small class="text-dark">{{ $history->keterangan ?? '-' }}</small>
                                            </div>
                                            {{-- HAPUS BAGIAN REFERENSI --}}
                                        </td>
                                        <td class="text-center">
                                            <small>{{ $history->user->name ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('stock-history.show', $history->produk_id) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               data-toggle="tooltip" 
                                               title="Lihat History Produk Ini">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Belum ada history stock
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($histories->hasPages())
                        <div class="card-footer">
                            {{ $histories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .table td {
            vertical-align: middle;
        }
        code {
            font-size: 0.9em;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .fw-semibold {
            font-weight: 600;
        }
        .badge {
            font-size: 0.85rem;
            padding: 4px 8px;
        }
        .barcode-visual {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .barcode-visual img {
            max-width: 120px;
            height: auto;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.875rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush