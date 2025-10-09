@extends('layout.master')

@section('title', 'Edit Pembelian')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Pembelian</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('pembelian.update', $pembelian->pembelian_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal Pembelian</label>
                                    <input type="date"
                                           name="tanggal"
                                           id="tanggal"
                                           class="form-control @error('tanggal') is-invalid @enderror"
                                           value="{{ old('tanggal', $pembelian->tanggal) }}"
                                           required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id">Supplier</label>
                                    <select name="supplier_id"
                                            id="supplier_id"
                                            class="form-control @error('supplier_id') is-invalid @enderror">
                                        <option value="">-- Pilih Supplier --</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->supplier_id }}"
                                                    {{ old('supplier_id', $pembelian->supplier_id) == $supplier->supplier_id ? 'selected' : '' }}>
                                                {{ $supplier->nama_supplier }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Detail Produk</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="produkTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="25%">Produk</th>
                                                <th width="15%">Kategori</th>
                                                <th width="10%">Satuan</th>
                                                <th width="10%">Jumlah</th>
                                                <th width="15%">Harga Beli</th>
                                                <th width="15%">Subtotal</th>
                                                <th width="10%">
                                                    <button type="button" class="btn btn-success btn-sm" id="addRow">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pembelian->details as $index => $detail)
                                            <tr>
                                                <td>
                                                    <select name="produk_id[]"
                                                            class="form-control produk-select @error('produk_id.'.$index) is-invalid @enderror"
                                                            required>
                                                        <option value="">-- Pilih Produk --</option>
                                                        @foreach($produks as $produk)
                                                            <option value="{{ $produk->produk_id }}"
                                                                    data-kategori="{{ $produk->kategori->nama_kategori ?? '' }}"
                                                                    data-satuan="{{ $produk->satuan->nama_satuan ?? '' }}"
                                                                    data-harga="{{ $produk->harga_beli ?? 0 }}"
                                                                    {{ old('produk_id.'.$index, $detail->produk_id) == $produk->produk_id ? 'selected' : '' }}>
                                                                {{ $produk->nama_produk }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('produk_id.'.$index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control kategori-display"
                                                           value="{{ $detail->produk->kategori->nama_kategori ?? '' }}"
                                                           readonly>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control satuan-display"
                                                           value="{{ $detail->produk->satuan->nama_satuan ?? '' }}"
                                                           readonly>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           name="jumlah[]"
                                                           class="form-control jumlah @error('jumlah.'.$index) is-invalid @enderror"
                                                           value="{{ old('jumlah.'.$index, $detail->jumlah) }}"
                                                           min="1"
                                                           required>
                                                    @error('jumlah.'.$index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           step="0.01"
                                                           name="harga_beli[]"
                                                           class="form-control harga-beli @error('harga_beli.'.$index) is-invalid @enderror"
                                                           value="{{ old('harga_beli.'.$index, $detail->harga_beli) }}"
                                                           min="0"
                                                           readonly>
                                                    @error('harga_beli.'.$index)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control subtotal"
                                                           readonly>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-right">Total Keseluruhan:</th>
                                                <th>
                                                    <span class="total-harga font-weight-bold text-primary">Rp 0</span>
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Pembelian
                                    </button>
                                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to format number to Indonesian Rupiah
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    }

    // Function to calculate subtotal for a row
    function calculateSubtotal(row) {
        const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
        const hargaBeli = parseFloat(row.querySelector('.harga-beli').value) || 0;
        const subtotal = jumlah * hargaBeli;

        row.querySelector('.subtotal').value = formatRupiah(subtotal);
        return subtotal;
    }

    // Function to calculate total
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('#produkTable tbody tr').forEach(function(row) {
            total += calculateSubtotal(row);
        });

        document.querySelector('.total-harga').textContent = formatRupiah(total);
    }

    // Handle product selection change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const row = e.target.closest('tr');

            if (selectedOption.value) {
                // Set kategori
                row.querySelector('.kategori-display').value = selectedOption.getAttribute('data-kategori') || '';
                // Set satuan
                row.querySelector('.satuan-display').value = selectedOption.getAttribute('data-satuan') || '';
                // Set harga beli default (optional)
                const defaultHarga = selectedOption.getAttribute('data-harga') || 0;
                if (parseFloat(row.querySelector('.harga-beli').value) === 0) {
                    row.querySelector('.harga-beli').value = defaultHarga;
                }
            } else {
                // Reset if no product selected
                row.querySelector('.kategori-display').value = '';
                row.querySelector('.satuan-display').value = '';
            }
            calculateTotal();
        }
    });

    // Handle quantity and price changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('jumlah') || e.target.classList.contains('harga-beli')) {
            calculateTotal();
        }
    });

    // Add new row
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#produkTable tbody');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);

        // Reset all inputs in the new row
        newRow.querySelectorAll('input').forEach(function(input) {
            if (input.classList.contains('jumlah')) {
                input.value = '1';
            } else if (input.classList.contains('harga-beli')) {
                input.value = '0';
            } else {
                input.value = '';
            }
        });

        // Reset select
        newRow.querySelectorAll('select').forEach(function(select) {
            select.selectedIndex = 0;
        });

        // Clear readonly fields
        newRow.querySelector('.kategori-display').value = '';
        newRow.querySelector('.satuan-display').value = '';
        newRow.querySelector('.subtotal').value = '';

        tbody.appendChild(newRow);
        calculateTotal();
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('#produkTable tbody tr');

            if (rows.length > 1) {
                e.target.closest('tr').remove();
                calculateTotal();
            } else {
                alert('Minimal harus ada satu produk!');
            }
        }
    });

    // Calculate initial total when page loads
    calculateTotal();
});
</script>

@endsection
