<?php

namespace App\Http\Controllers;

use App\Models\Pelanggans;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\{Province, City, District, Village};

/**
 * PelangganController
 * Controller untuk mengelola data pelanggan (CRUD)
 * Menggunakan package Laravolt Indonesia untuk data wilayah Indonesia
 * (Provinsi, Kota/Kabupaten, Kecamatan, Kelurahan/Desa)
 */
class PelangganController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua pelanggan beserta data wilayahnya
     * Flow:
     * 1. Query semua pelanggan dengan eager loading relasi wilayah
     *    (province, city, district, village)
     * 2. Kirim data ke view pelanggan.index
     *
     * Return: View dengan daftar pelanggan lengkap dengan alamat wilayah
     * Note: Eager loading mencegah N+1 query problem saat menampilkan wilayah
     */
    public function index()
    {
        // Ambil semua pelanggan dengan relasi wilayah untuk efisiensi query
        $pelanggans = Pelanggans::with(['province', 'city', 'district', 'village'])->get();

        return view('pelanggan.index', compact('pelanggans'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form tambah pelanggan baru
     * Flow:
     * 1. Ambil data semua provinsi dari database Indonesia
     * 2. Format data provinsi menjadi array (code => name) untuk dropdown
     * 3. Kirim data provinsi ke view form
     *
     * Return: View form create dengan dropdown provinsi
     * Note: Kota, kecamatan, desa akan dimuat secara dinamis via AJAX
     *       berdasarkan pilihan user (dependent dropdown)
     */
    public function create()
    {
        // Ambil semua provinsi untuk dropdown, format: code sebagai key, name sebagai value
        $provinces = Province::pluck('name', 'code');

        return view('pelanggan.create', compact('provinces'));
    }

    /**
     * store()
     * Fungsi: Menyimpan data pelanggan baru ke database
     * Flow:
     * 1. Validasi semua input form:
     *    - nama_pelanggan: wajib, max 100 karakter
     *    - alamat: wajib diisi (alamat detail/jalan)
     *    - nomor_telepon: wajib, max 15 karakter
     *    - province_id, city_id, district_id, village_id: wajib dipilih
     * 2. Simpan data pelanggan ke database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: Request $request (data dari form)
     * Return: Redirect ke pelanggan.index dengan flash message
     */
    public function store(Request $request)
    {
        // Validasi input form pelanggan
        $request->validate([
            'nama_pelanggan' => 'required|max:100',    // Nama wajib, max 100 char
            'alamat'         => 'required',             // Alamat detail wajib
            'nomor_telepon'  => 'required|max:15',      // Nomor telepon wajib, max 15 char
            'province_id'    => 'required',             // Provinsi wajib dipilih
            'city_id'        => 'required',             // Kota/Kabupaten wajib dipilih
            'district_id'    => 'required',             // Kecamatan wajib dipilih
            'village_id'     => 'required',             // Kelurahan/Desa wajib dipilih
        ]);

        // Insert data pelanggan baru ke database
        Pelanggans::create($request->all());

        // Redirect ke daftar pelanggan dengan pesan sukses
        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    /**
     * show()
     * Fungsi: Menampilkan detail data pelanggan tertentu
     * Flow:
     * 1. Cari pelanggan berdasarkan ID
     * 2. Jika tidak ditemukan, throw 404 error
     * 3. Tampilkan view detail pelanggan
     *
     * Parameter: string $id (ID pelanggan)
     * Return: View detail pelanggan
     * Use case: Lihat informasi lengkap pelanggan tanpa edit
     */
    public function show(string $id)
    {
        // Cari pelanggan berdasarkan ID, error 404 jika tidak ada
        $pelanggan = Pelanggans::findOrFail($id);

        return view('pelanggan.show', compact('pelanggan'));
    }

    /**
     * edit()
     * Fungsi: Menampilkan form edit data pelanggan
     * Flow:
     * 1. Cari data pelanggan berdasarkan ID
     * 2. Ambil semua provinsi untuk dropdown
     * 3. Ambil data wilayah sesuai dengan data pelanggan yang ada:
     *    - Cities: berdasarkan province yang dipilih pelanggan
     *    - Districts: berdasarkan city yang dipilih pelanggan
     *    - Villages: berdasarkan district yang dipilih pelanggan
     * 4. Kirim semua data ke view edit
     *
     * Parameter: string $id (ID pelanggan yang akan diedit)
     * Return: View form edit dengan data pelanggan dan dropdown wilayah
     * Note: Dropdown wilayah sudah ter-filter sesuai data pelanggan
     *       sehingga user bisa melihat pilihan saat ini
     */
    public function edit(string $id)
    {
        // Cari pelanggan berdasarkan ID
        $pelanggan = Pelanggans::findOrFail($id);

        // Ambil semua provinsi untuk dropdown (tetap tampilkan semua)
        $provinces = Province::pluck('name', 'code');

        // Ambil data wilayah yang sudah ter-filter sesuai pilihan pelanggan
        // Ini untuk menampilkan dropdown yang sudah berisi pilihan saat ini
        $cities     = City::where('province_code', $pelanggan->province_id)->pluck('name', 'code');
        $districts  = District::where('city_code', $pelanggan->city_id)->pluck('name', 'code');
        $villages   = Village::where('district_code', $pelanggan->district_id)->pluck('name', 'code');

        // Kirim semua data ke view edit
        return view('pelanggan.edit', compact(
            'pelanggan',
            'provinces',
            'cities',
            'districts',
            'villages'
        ));
    }

    /**
     * update()
     * Fungsi: Memperbarui data pelanggan yang sudah ada
     * Flow:
     * 1. Validasi input form (sama seperti store)
     * 2. Cari pelanggan berdasarkan ID
     * 3. Update data pelanggan dengan data baru dari form
     * 4. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter:
     * - Request $request (data form yang sudah divalidasi)
     * - string $id (ID pelanggan yang akan diupdate)
     * Return: Redirect ke pelanggan.index dengan flash message
     */
    public function update(Request $request, string $id)
    {
        // Validasi input form (sama seperti saat create)
        $request->validate([
            'nama_pelanggan' => 'required|max:100',
            'alamat'         => 'required',
            'nomor_telepon'  => 'required|max:15',
            'province_id'    => 'required',
            'city_id'        => 'required',
            'district_id'    => 'required',
            'village_id'     => 'required',
        ]);

        // Cari pelanggan berdasarkan ID
        $pelanggan = Pelanggans::findOrFail($id);

        // Update data pelanggan dengan data baru
        $pelanggan->update($request->all());

        // Redirect ke daftar pelanggan dengan pesan sukses
        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * destroy()
     * Fungsi: Menghapus data pelanggan dari database
     * Flow:
     * 1. Cari pelanggan berdasarkan ID
     * 2. Hapus data pelanggan dari database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: string $id (ID pelanggan yang akan dihapus)
     * Return: Redirect ke pelanggan.index dengan flash message
     * Warning: Pastikan tidak ada transaksi penjualan yang masih terkait
     *          dengan pelanggan ini untuk menjaga integritas data
     */
    public function destroy(string $id)
    {
        // Cari pelanggan berdasarkan ID
        $pelanggan = Pelanggans::findOrFail($id);

        // Hapus data pelanggan dari database
        $pelanggan->delete();

        // Redirect ke daftar pelanggan dengan pesan sukses
        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
