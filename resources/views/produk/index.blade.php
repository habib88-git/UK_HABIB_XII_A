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
                                        
                                        {{-- ✅ KOLOM BARCODE - OPSI 1 --}}
                                        <td class="text-center">
                                            <div class="barcode-container">
                                                @php
                                                    // ✅ AMBIL BATCH AKTIF FIFO
                                                    $batchAktif = $produk->batches()
                                                        ->where('stok', '>', 0)
                                                        ->orderBy('kadaluwarsa', 'asc')
                                                        ->first();
                                                    
                                                    $barcodeDisplay = $batchAktif ? $batchAktif->barcode_batch : $produk->barcode;
                                                    $jumlahBatchAktif = $produk->batches()->where('stok', '>', 0)->count();
                                                @endphp
                                                
                                                @if($batchAktif)
                                                    @php
                                                        try {
                                                            echo DNS1D::getBarcodeHTML($barcodeDisplay, 'C128', 1.5, 40);
                                                        } catch (\Exception $e) {
                                                            echo '<div class="text-danger small">Barcode Error</div>';
                                                        }
                                                    @endphp
                                                    <small class="d-block mt-1">
                                                        <code>{{ $barcodeDisplay }}</code>
                                                    </small>
                                                    
                                                    <div class="mt-2">
                                                        <span class="badge badge-success" data-toggle="tooltip" 
                                                              title="Batch FIFO - Akan terjual pertama">
                                                            <i class="fas fa-check-circle"></i> Batch Aktif
                                                        </span>
                                                        
                                                        @if($jumlahBatchAktif > 1)
                                                            <span class="badge badge-info" data-toggle="tooltip" 
                                                                  title="Total {{ $jumlahBatchAktif }} batch dengan stok tersedia">
                                                                <i class="fas fa-layer-group"></i> {{ $jumlahBatchAktif }} Batch
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="fas fa-box"></i> Stok batch: {{ $batchAktif->stok }}
                                                    </small>
                                                @else
                                                    @php
                                                        try {
                                                            echo DNS1D::getBarcodeHTML($produk->barcode, 'C128', 1.5, 40);
                                                        } catch (\Exception $e) {
                                                            echo '<div class="text-danger small">Barcode Error</div>';
                                                        }
                                                    @endphp
                                                    <small class="d-block mt-1">
                                                        <code>{{ $produk->barcode }}</code>
                                                    </small>
                                                    <span class="badge badge-warning mt-2">
                                                        <i class="fas fa-exclamation-triangle"></i> Stok Habis
                                                    </span>
                                                    <small class="text-muted d-block mt-1">Barcode Master</small>
                                                @endif
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
                                        
                                        {{-- ✅ STOK DIHITUNG DARI BATCH - OPSI 1 --}}
                                        <td class="text-center">
                                            @php
                                                $totalStok = $produk->batches->sum('stok');
                                                $jumlahBatch = $produk->batches->count();
                                                $jumlahBatchAktif = $produk->batches->where('stok', '>', 0)->count();
                                            @endphp
                                            
                                            <div class="stok-info">
                                                <span class="badge badge-{{ $totalStok > 10 ? 'success' : ($totalStok > 0 ? 'warning' : 'danger') }}" 
                                                      style="font-size: 1rem; padding: 6px 12px;">
                                                    <i class="fas fa-boxes"></i> {{ $totalStok }}
                                                </span>
                                                
                                                @if($jumlahBatch > 0)
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-layer-group"></i> 
                                                            {{ $jumlahBatchAktif }} aktif / {{ $jumlahBatch }} total
                                                        </small>
                                                    </div>
                                                @else
                                                    <div class="mt-2">
                                                        <small class="text-muted">Belum ada batch</small>
                                                    </div>
                                                @endif
                                            </div>
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
        
        /* ✅ STYLES BARU UNTUK OPSI 1 */
        .barcode-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            min-height: 120px;
        }
        
        .barcode-container code {
            font-size: 0.875rem;
            background: #f8f9fa;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            letter-spacing: 0.5px;
        }
        
        .barcode-container .badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            margin: 0 2px;
            font-weight: 600;
        }
        
        .barcode-container small {
            font-size: 0.75rem;
            line-height: 1.4;
        }
        
        .stok-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .stok-info .badge {
            font-weight: 700;
        }
        
        /* Hover effect untuk badge */
        .badge[data-toggle="tooltip"] {
            cursor: help;
            transition: all 0.2s;
        }
        
        .badge[data-toggle="tooltip"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        /* Animasi untuk barcode */
        .barcode-container:hover {
            background-color: rgba(13, 110, 253, 0.03);
            border-radius: 8px;
            transition: background-color 0.2s;
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
                "order": [[0, "asc"]],
                "pageLength": 25,
                "columnDefs": [
                    { "orderable": false, "targets": [1, 2, 11] } // Barcode, Photo, Aksi
                ]
            });

            // ✅ Inisialisasi tooltip
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                delay: { show: 300, hide: 100 }
            });
            
            // ✅ Refresh tooltip saat ganti halaman
            $('#dataTableHover').on('page.dt', function() {
                setTimeout(function() {
                    $('[data-toggle="tooltip"]').tooltip();
                }, 100);
            });
        });
    </script>
@endpush