@extends('layout.master')

@section('title', 'Edit Kategori')

@section('content')
    <div class="container-fluid" id="container-wrapper">
        <h2 class="mb-4">Edit Kategori</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('kategori.update', $kategori->kategori_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control"
                    value="{{ old('nama_kategori', $kategori->nama_kategori) }}" required>
            </div>
            <button class="btn btn-success">Update</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection
