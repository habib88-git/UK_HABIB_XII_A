@extends('layout.master')
@section('title', 'Daftar Penjualan')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shopping-cart"></i> Daftar Penjualan
        </h1>
        <a href="{{ route('penjualan.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Penjualan
        </a>
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
                    <h6 class="m-0 font-weight-bold text-primary">Data Penjualan</h6>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-bordered align-items-center table-hover" id="dataTableHover">
                        <thead class="thead-light text-center">
                            <tr>
                                <th style="width:5%">No</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Kasir</th>
                                <th style="width:10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penjualans as $p)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($p->tanggal_penjualan)->format('d-m-Y') }}
                                    </td>
                                    <td>{{ $p->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                    <td class="fw-bold text-end">
                                        @if ($p->diskon > 0)
                                            <div>
                                                <span class="text-success">
                                                    Rp {{ number_format($p->total_harga - $p->diskon, 0, ',', '.') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <s>Rp {{ number_format($p->total_harga, 0, ',', '.') }}</s>
                                                </small>
                                            </div>
                                        @else
                                            Rp {{ number_format($p->total_harga, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $p->pembayaran->metode ?? '-' }}</td>
                                    <td>{{ $p->user->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('penjualan.show', $p->penjualan_id) }}"
                                            class="btn btn-sm btn-outline-info" data-toggle="tooltip"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> Belum ada data penjualan
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTableHover').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            },
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: 6 }
            ]
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
