<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Fonts & CSS -->
    <link href="{{ asset('ruang-admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('ruang-admin/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('ruang-admin/css/ruang-admin.min.css') }}" rel="stylesheet">
    <link href="{{ asset('ruang-admin/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        @include('layout.sidebar')
        <!-- Sidebar -->

        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                @include('layout.header')

                <div class="container-fluid" id="container-wrapper">
                    @yield('content')
                </div>
            </div>

            <!-- End of Main Content -->

            @include('layout.footer')

        </div>

        <!-- Scroll to top -->
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>

    <!-- Scripts - URUTAN INI PENTING! -->
    <script src="{{ asset('ruang-admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('ruang-admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('ruang-admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('ruang-admin/js/ruang-admin.min.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('ruang-admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('ruang-admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false,
                position: 'center',
                toast: true,
            });
        </script>
    @endif

    <script>
        // Auto hide alert
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);

        // Initialize tooltip
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>

    <!-- Stack untuk script tambahan dari child view -->
    @stack('scripts')

    @yield('scripts')
</body>

</html>
