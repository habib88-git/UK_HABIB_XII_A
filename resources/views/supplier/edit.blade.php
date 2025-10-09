@extends('layout.master')

@section('title', 'Edit Supplier')

@section('content')
<div class="container-fluid" id="container-wrapper">
    <h2 class="mb-4">Edit Supplier</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('supplier.update', $supplier->supplier_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama_supplier" class="form-control" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" required>{{ old('alamat', $supplier->alamat) }}</textarea>
        </div>
        <div class="mb-3">
            <label>Telepon</label>
            <input type="number" name="telp" class="form-control" value="{{ old('telp', $supplier->telp) }}" required>
        </div>
        <button class="btn btn-success">Update</button>
        <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
