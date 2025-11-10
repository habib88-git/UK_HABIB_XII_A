<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

// DASHBOARD
Route::get('/admindashboard', [DashboardController::class, 'admindashboard'])
    ->name('admin.index')
    ->middleware('admin');

Route::get('/penjualan/create', [PenjualanController::class, 'create'])
    ->name('penjualan.create')
    ->middleware('kasir');

// AUTH
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

// Routes User
Route::get('/user', [UserController::class, 'index'])->name('users.index')->middleware('admin');
Route::get('/user/create', [UserController::class, 'create'])->name('users.create')->middleware('admin');
Route::post('/user', [UserController::class, 'store'])->name('users.store')->middleware('admin');
Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('admin');
Route::put('/user/{id}', [UserController::class, 'update'])->name('users.update')->middleware('admin');
Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('admin');

// PELANGGAN - admin & kasir bisa akses
Route::middleware(['auth'])->group(function () {
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');
    Route::get('/pelanggan/create', [PelangganController::class, 'create'])->name('pelanggan.create');
    Route::post('/pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');

    // route edit, update, delete hanya untuk admin
    Route::middleware('admin')->group(function () {
        Route::get('/pelanggan/{id}/edit', [PelangganController::class, 'edit'])->name('pelanggan.edit');
        Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('pelanggan.update');
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show'])->name('pelanggan.show');
        Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');
    });
});

// Routes Kategori
Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index')->middleware('admin');
Route::get('/kategori/create', [KategoriController::class, 'create'])->name('kategori.create')->middleware('admin');
Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store')->middleware('admin');
Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit')->middleware('admin');
Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update')->middleware('admin');
Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy')->middleware('admin');

// Routes Satuan
Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index')->middleware('admin');
Route::get('/satuan/create', [SatuanController::class, 'create'])->name('satuan.create')->middleware('admin');
Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store')->middleware('admin');
Route::get('/satuan/{id}/edit', [SatuanController::class, 'edit'])->name('satuan.edit')->middleware('admin');
Route::put('/satuan/{id}', [SatuanController::class, 'update'])->name('satuan.update')->middleware('admin');
Route::delete('/satuan/{id}', [SatuanController::class, 'destroy'])->name('satuan.destroy')->middleware('admin');

// Routes Penjualan (kasir)
Route::middleware(['kasir'])->group(function () {
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
    Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');

    // 🔥 ROUTE BARU UNTUK CETAK STRUK
    Route::get('/penjualan/struk/{id}', [PenjualanController::class, 'cetakStruk'])->name('penjualan.struk');

    Route::post('/penjualan/{id}/bayar', [PenjualanController::class, 'bayar'])->name('penjualan.bayar');
});

// Resource route (pastikan ini di bawah route manual di atas)
Route::resource('penjualan', PenjualanController::class)->middleware('kasir');

Route::post('/produk/scan-barcode', [ProdukController::class, 'scanBarcode'])->name('produk.scan');
Route::get('/produk/barcode/{id}', [ProdukController::class, 'cetakBarcode'])->name('produk.cetakBarcode');
Route::get('/produk/barcode-semua', [ProdukController::class, 'cetakSemuaBarcode'])->name('produk.cetakSemuaBarcode');

Route::resource('produk', ProdukController::class)->middleware('admin');
Route::resource('penjualan', PenjualanController::class)->middleware('kasir');
Route::resource('supplier', SupplierController::class)->middleware('admin');
Route::resource('pembelian', PembelianController::class)->middleware('admin');

Route::get('/profit', [ProfitController::class, 'index'])->name('profit.index')->middleware('admin');

Route::prefix('laporan')->middleware('admin')->group(function () {
    Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/cetak', [LaporanController::class, 'cetakPdf'])->name('laporan.cetak');
    Route::get('/{id}/struk', [LaporanController::class, 'struk'])->name('laporan.struk');
});

Route::get('api/cities/{province_id}', function ($province_id) {
    return \Laravolt\Indonesia\Models\City::where('province_id', $province_id)->pluck('name', 'id');
});
