@extends('layout.master')

@section('title', 'Daftar Produk')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-primary">
            <i class="fas fa-box"></i> Daftar Produk
        </h3>
        <a href="{{ route('produk.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    {{-- Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th style="width: 12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">
                                    @if($produk->photo)
                                        <img src="{{ asset('storage/' . $produk->photo) }}"
                                             alt="{{ $produk->nama_produk }}"
                                             width="60" height="60"
                                             style="object-fit: cover; border-radius: 5px;">
                                    @else
                                        <span class="text-muted">Tidak ada foto</span>
                                    @endif
                                </td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->kategori->nama_kategori ?? '-' }}</td>
                                <td>{{ $produk->satuan->nama_satuan ?? '-' }}</td>
                                <td>Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $produk->stok }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('produk.edit', $produk->produk_id) }}"
                                           class="btn btn-sm btn-outline-warning"
                                           data-toggle="tooltip" title="Edit Produk">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('produk.destroy', $produk->produk_id) }}"
                                              method="POST"
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
                                <td colspan="9" class="text-center text-muted py-4">
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
@endsection

@section('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
