@extends('layout.master')

@section('title', 'Edit Satuan')

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <h2 class="mb-4">Edit Satuan</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('satuan.update', $satuan->satuan_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nama Satuan</label>
                <input type="text" name="nama_satuan" class="form-control" value="{{ old('nama_satuan', $satuan->nama_satuan) }}" required>
            </div>
            <button class="btn btn-success">Update</button>
            <a href="{{ route('satuan.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection
