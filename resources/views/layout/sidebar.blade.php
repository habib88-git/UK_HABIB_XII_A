<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
        <div class="sidebar-brand-icon">
            <img src="img/logo/logo2.png" alt="Logo">
        </div>
        <div class="sidebar-brand-text mx-3">RuangAdmin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="{{ route('admin.index')}}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Fitur
    </div>

    <!-- Manajemen User -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('users.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Manajemen User</span>
        </a>
    </li>

    <!-- Pelanggan -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('pelanggan.index') }}">
            <i class="fas fa-fw fa-user-friends"></i>
            <span>Pelanggan</span>
        </a>
    </li>

    <!-- Produk -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduk"
           aria-expanded="false" aria-controls="collapseProduk">
            <i class="fas fa-fw fa-box"></i>
            <span>Produk</span>
        </a>
        <div id="collapseProduk" class="collapse" aria-labelledby="headingProduk" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('kategori.index') }}">Kategori</a>
                <a class="collapse-item" href="{{ route('satuan.index') }}">Unit</a>
                <a class="collapse-item" href="{{ route('produk.index') }}">Produk</a>
            </div>
        </div>
    </li>

    <!-- Pembelian -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePembelian"
           aria-expanded="false" aria-controls="collapsePembelian">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Pembelian</span>
        </a>
        <div id="collapsePembelian" class="collapse" aria-labelledby="headingPembelian" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="{{ route('supplier.index') }}">Supplier</a>
                <a class="collapse-item" href="{{ route('pembelian.index') }}">Pembelian</a>
            </div>
        </div>
    </li>

    <!-- Transaksi -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('penjualan.index') }}">
            <i class="fas fa-fw fa-cash-register"></i>
            <span>Transaksi</span>
        </a>
    </li>

    <!-- Laporan -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('laporan.index') }}">
            <i class="fas fa-fw fa-file-alt"></i>
            <span>Laporan</span>
        </a>
    </li>

</ul>
