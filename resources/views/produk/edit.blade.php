@extends('layout.master')
@section('title', 'Edit Produk')

@section('content')
    <div class="container-fluid" id="container-wrapper">

        {{-- Header --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Edit Produk
            </h1>
        </div>

        {{-- Alert Error --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Alert Info Batch --}}
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Perhatian!</strong>
            <ul class="mb-0 mt-2">
                <li><strong>Stok & Kadaluwarsa:</strong> Tidak bisa diedit di sini (dikelola otomatis dari batch)</li>
                <li><strong>Lihat Batch:</strong> Scroll ke bawah untuk melihat detail semua batch produk ini</li>
                <li><strong>Tambah Stok:</strong> Gunakan menu <strong>Pembelian</strong> untuk menambah batch baru</li>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        {{-- Form Edit Produk --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-pen me-1"></i> Form Edit Produk
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('produk.update', $produk->produk_id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" id="barcode" name="barcode" class="form-control"
                                value="{{ old('barcode', $produk->barcode) }}" readonly>
                            <small class="text-muted">Barcode tidak dapat diubah</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="nama_produk" name="nama_produk" class="form-control"
                                value="{{ old('nama_produk', $produk->nama_produk) }}" required maxlength="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_beli_display" class="form-control"
                                    value="{{ number_format(old('harga_beli', $produk->harga_beli), 0, ',', '.') }}"
                                    placeholder="0" required>
                                <input type="hidden" id="harga_beli" name="harga_beli"
                                    value="{{ old('harga_beli', $produk->harga_beli) }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="harga_jual_display" class="form-control"
                                    value="{{ number_format(old('harga_jual', $produk->harga_jual), 0, ',', '.') }}"
                                    placeholder="0" required>
                                <input type="hidden" id="harga_jual" name="harga_jual"
                                    value="{{ old('harga_jual', $produk->harga_jual) }}">
                            </div>
                        </div>

                        {{-- ✅ STOK READ-ONLY (DIHITUNG DARI BATCH) --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Stok (Dari Semua Batch)</label>
                            <input type="text" class="form-control bg-light" 
                                value="{{ $produk->stokTersedia() }} unit (dari {{ $produk->batches->count() }} batch)" readonly>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Stok dihitung otomatis dari batch
                            </small>
                        </div>

                        {{-- ✅ KADALUWARSA TERDEKAT (READ-ONLY) --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kadaluwarsa Terdekat</label>
                            @php
                                $batchTerdekat = $produk->getBatchTerdekat();
                            @endphp
                            <input type="text" class="form-control bg-light" 
                                value="{{ $batchTerdekat ? $batchTerdekat->kadaluwarsa->format('d/m/Y') : 'Tidak ada batch' }}" readonly>
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> Dari batch dengan kadaluwarsa paling dekat
                            </small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kategori_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select id="kategori_id" name="kategori_id" class="form-control" required>
                                <option disabled>-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->kategori_id }}"
                                        {{ old('kategori_id', $produk->kategori_id) == $kategori->kategori_id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="satuan_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select id="satuan_id" name="satuan_id" class="form-control" required>
                                <option disabled>-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->satuan_id }}"
                                        {{ old('satuan_id', $produk->satuan_id) == $satuan->satuan_id ? 'selected' : '' }}>
                                        {{ $satuan->nama_satuan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option disabled>-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                        {{ old('supplier_id', $produk->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                        {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Foto Produk</label>
                            @if ($produk->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $produk->photo) }}" alt="{{ $produk->nama_produk }}"
                                        width="120" style="object-fit: cover; border-radius: 8px;">
                                </div>
                            @endif
                            <input type="file" id="photo" name="photo" class="form-control"
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Upload foto baru jika ingin mengganti. Maksimal 2MB.</small>
                        </div>
                    </div>

                    {{-- Barcode Visual (Full Width) --}}
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-center d-block">Barcode Visual</label>
                            <div class="card">
                                <div class="card-body text-center py-4">
                                    @php
                                        try {
                                            echo '<div class="mb-3 d-flex justify-content-center">';
                                            echo DNS1D::getBarcodeHTML($produk->barcode, 'C128', 2.5, 80);
                                            echo '</div>';
                                            echo '<h5 class="mb-2">' . $produk->barcode . '</h5>';
                                            echo '<small class="text-muted">Format: CODE128</small>';
                                        } catch (\Exception $e) {
                                            echo '<div class="text-danger">Barcode Error: ' .
                                                $produk->barcode .
                                                '</div>';
                                        }
                                    @endphp
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                        <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ✅ TABEL BATCH PRODUK --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-layer-group me-2"></i> Detail Batch Produk
                </h6>
            </div>
            <div class="card-body">
                @if($produk->batches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Barcode Batch</th>
                                    <th width="10%">Stok</th>
                                    <th width="15%">Kadaluwarsa</th>
                                    <th width="15%">Harga Beli</th>
                                    <th width="15%">Sumber</th>
                                    <th width="15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($produk->batches as $index => $batch)
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $kadaluwarsa = \Carbon\Carbon::parse($batch->kadaluwarsa);
                                        $diff = $now->diffInDays($kadaluwarsa, false);
                                        
                                        if ($diff < 0) {
                                            $badgeColor = 'danger';
                                            $statusText = 'Expired ' . abs($diff) . ' hari lalu';
                                        } elseif ($diff == 0) {
                                            $badgeColor = 'danger';
                                            $statusText = 'Kadaluwarsa hari ini';
                                        } elseif ($diff <= 30) {
                                            $badgeColor = 'warning';
                                            $statusText = $diff . ' hari lagi';
                                        } else {
                                            $badgeColor = 'success';
                                            $statusText = $diff . ' hari lagi';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><code>{{ $batch->barcode_batch }}</code></td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $batch->stok > 0 ? 'success' : 'secondary' }}">
                                                {{ $batch->stok }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            {{ $batch->kadaluwarsa->format('d/m/Y') }}
                                        </td>
                                        <td class="text-end">Rp {{ number_format($batch->harga_beli, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($batch->pembelian_id)
                                                <a href="{{ route('pembelian.show', $batch->pembelian_id) }}" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-receipt"></i> Pembelian #{{ $batch->pembelian_id }}
                                                </a>
                                            @else
                                                <span class="badge badge-secondary">Stok Awal</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $badgeColor }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total Stok:</th>
                                    <th class="text-center">
                                        <span class="badge badge-primary">{{ $produk->batches->sum('stok') }}</span>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info text-center mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Belum ada batch untuk produk ini. Tambahkan stok melalui menu <strong>Pembelian</strong>.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Format Rupiah otomatis untuk Harga Beli
        document.getElementById('harga_beli_display').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, ''); // Ambil hanya angka
            document.getElementById('harga_beli').value = value; // Simpan angka mentah
            this.value = formatRupiah(value); // Tampilkan format Rupiah
        });

        // Format Rupiah otomatis untuk Harga Jual
        document.getElementById('harga_jual_display').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, ''); // Ambil hanya angka
            document.getElementById('harga_jual').value = value; // Simpan angka mentah
            this.value = formatRupiah(value); // Tampilkan format Rupiah
        });

        // Fungsi format Rupiah
        function formatRupiah(angka) {
            if (!angka) return '0';
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
@endpush