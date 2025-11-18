@extends('layout.master')
@section('title', 'Daftar Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box"></i> Daftar Produk
            </h1>
            <div>
                <a href="{{ route('produk.cetakSemuaBarcode') }}" target="_blank" class="btn btn-success shadow-sm mr-2">
                    <i class="fas fa-barcode"></i> Cetak Semua Barcode
                </a>
                <a href="{{ route('produk.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-check-circle"></i></strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- DataTable Card --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Data Produk</h6>
                    </div>
                    <div class="table-responsive p-3">
                        <table class="table table-bordered align-items-center table-hover" id="dataTableHover">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th style="width: 10%">Barcode</th>
                                    <th style="width: 10%">Foto</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Supplier</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Stok</th>
                                    <th>Kadaluwarsa Terdekat</th>
                                    <th style="width: 16%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produks as $produk)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            <div class="barcode-container">
                                                @php
                                                    // Langsung pakai CODE128 aja biar universal
                                                    try {
                                                        echo DNS1D::getBarcodeHTML($produk->barcode, 'C128', 1.5, 40);
                                                    } catch (\Exception $e) {
                                                        echo '<div class="text-danger small">Barcode Error</div>';
                                                    }
                                                @endphp
                                                <small class="d-block mt-1">{{ $produk->barcode }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if ($produk->photo)
                                                <img src="{{ asset('storage/' . $produk->photo) }}"
                                                    alt="{{ $produk->nama_produk }}" width="60" height="60"
                                                    style="object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $produk->nama_produk }}</td>
                                        <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
                                        <td>{{ $produk->satuan->nama_satuan ?? '-' }}</td>
                                        <td>{{ $produk->supplier->nama_supplier ?? '-' }}</td>
                                        <td>Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                                        
                                        {{-- ✅ STOK DIHITUNG DARI BATCH --}}
                                        <td class="text-center">
                                            @php
                                                $totalStok = $produk->batches->sum('stok');
                                            @endphp
                                            <span class="badge badge-{{ $totalStok > 10 ? 'success' : ($totalStok > 0 ? 'warning' : 'danger') }}">
                                                {{ $totalStok }}
                                            </span>
                                            @if($produk->batches->count() > 0)
                                                <br>
                                                <small class="text-muted">{{ $produk->batches->count() }} batch</small>
                                            @endif
                                        </td>

                                        {{-- ✅ KADALUWARSA DARI BATCH TERDEKAT --}}
                                        <td class="text-center">
                                            @php
                                                $batchTerdekat = $produk->getBatchTerdekat();
                                                if ($batchTerdekat) {
                                                    $now = \Carbon\Carbon::now();
                                                    $kadaluwarsa = \Carbon\Carbon::parse($batchTerdekat->kadaluwarsa);
                                                    $diff = $now->diffInDays($kadaluwarsa, false);
                                                    $color = $diff < 0 ? 'danger' : ($diff <= 30 ? 'warning' : 'success');
                                                } else {
                                                    $color = 'secondary';
                                                }
                                            @endphp

                                            @if($batchTerdekat)
                                                <span class="badge badge-{{ $color }}">
                                                    {{ $batchTerdekat->kadaluwarsa->format('d/m/Y') }}
                                                </span>
                                                <div class="mt-1" style="font-size: 12px;">
                                                    @if ($diff < 0)
                                                        <span class="text-{{ $color }}">Expired {{ abs($diff) }} hari lalu</span>
                                                    @elseif ($diff == 0)
                                                        <span class="text-{{ $color }}">Kadaluwarsa hari ini</span>
                                                    @else
                                                        <span class="text-{{ $color }}">{{ $diff }} hari lagi</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge badge-secondary">Tidak ada batch</span>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center" style="gap: 8px;">
                                                <a href="{{ route('produk.edit', $produk->produk_id) }}"
                                                    class="btn btn-sm btn-outline-warning" data-toggle="tooltip"
                                                    title="Edit Produk">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('produk.cetakBarcode', $produk->produk_id) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-secondary"
                                                    data-toggle="tooltip" title="Cetak Barcode">
                                                    <i class="fas fa-barcode"></i>
                                                </a>
                                                <form action="{{ route('produk.destroy', $produk->produk_id) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        data-toggle="tooltip" title="Hapus Produk">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> Belum ada data produk
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .btn-group .btn {
            margin-right: 8px;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .d-flex.justify-content-center {
            padding: 4px 0;
        }

        .d-flex.justify-content-center .btn,
        .d-flex.justify-content-center form {
            margin: 0 4px;
        }

        .d-flex.justify-content-center .btn {
            min-width: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#dataTableHover').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "order": [
                    [0, "asc"]
                ]
            });

            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush