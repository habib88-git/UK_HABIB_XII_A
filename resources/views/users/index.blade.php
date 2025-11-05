@extends('layout.master')
@section('title', 'Daftar User')

@section('content')
<div class="container-fluid" id="container-wrapper">

    {{-- Header --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar User</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Tambah User
        </a>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
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
                    <h6 class="m-0 font-weight-bold text-primary">Data User</h6>
                </div>
                <div class="table-responsive p-3">
                    <table class="table align-items-center table-flush table-hover" id="dataTableHover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telp</th>
                                <th>Alamat</th>
                                <th>Akses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->no_telp }}</td>
                                <td>{{ $user->alamat ?? '-' }}</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge badge-primary">Admin</span>
                                    @elseif($user->role == 'pemilik')
                                        <span class="badge badge-success">Pemilik</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($user->role) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user->user_id) }}"
                                       class="btn btn-sm btn-warning"
                                       data-toggle="tooltip" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user->user_id) }}"
                                          method="POST"
                                          style="display:inline-block;"
                                          onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                data-toggle="tooltip" title="Hapus User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data user</td>
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
    $(document).ready(function () {
        $('#dataTableHover').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
