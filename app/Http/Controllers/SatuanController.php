<?php

namespace App\Http\Controllers;

use App\Models\Satuans;
use Illuminate\Http\Request;

/**
 * SatuanController
 * Controller untuk mengelola data satuan produk (CRUD)
 * Satuan: unit pengukuran produk seperti pcs, box, kg, liter, dll
 * Menggunakan Resource Controller pattern dari Laravel
 */
class SatuanController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua satuan produk
     * Flow:
     * 1. Ambil semua data satuan dari database
     * 2. Kirim data ke view satuan.index
     *
     * Return: View dengan daftar satuan
     * Use case: Admin melihat daftar satuan yang tersedia (pcs, box, kg, dll)
     */
    public function index()
    {
        // Ambil semua data satuan
        $satuans = Satuans::all();

        // Tampilkan view index dengan data satuan
        return view('satuan.index', compact('satuans'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form untuk membuat satuan baru
     * Flow:
     * 1. Tampilkan form input satuan baru
     *
     * Return: View form tambah satuan
     * Use case: Admin ingin menambah satuan baru (misal: karton, lusin, dll)
     * Note: Form sederhana, hanya input nama_satuan
     */
    public function create()
    {
        // Tampilkan form create satuan
        return view('satuan.create');
    }

    /**
     * store()
     * Fungsi: Menyimpan satuan baru ke database
     * Flow:
     * 1. Validasi input nama_satuan (wajib diisi, max 50 karakter)
     * 2. Simpan data satuan ke database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: Request $request (berisi data dari form)
     * Return: Redirect ke satuan.index dengan flash message
     * Contoh data: "pcs", "box", "kg", "liter", "meter", dll
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'nama_satuan' => 'required|string|max:50', // Nama satuan wajib, string, max 50 char
        ]);

        // Insert data satuan baru ke database
        Satuans::create($request->all());

        // Redirect ke halaman daftar satuan dengan flash message sukses
        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * show()
     * Fungsi: Menampilkan detail satuan tertentu (tidak digunakan)
     * Parameter: string $id (ID satuan)
     * Note: Fungsi ini kosong karena satuan tidak memerlukan halaman detail
     *       Data satuan sangat sederhana (hanya nama), cukup ditampilkan di list
     */
    public function show(string $id)
    {
        // Fungsi show tidak diimplementasikan
        // Satuan tidak perlu halaman detail karena datanya simple
    }

    /**
     * edit()
     * Fungsi: Menampilkan form untuk edit satuan
     * Flow:
     * 1. Cari satuan berdasarkan ID
     * 2. Jika tidak ditemukan, akan throw 404 error
     * 3. Kirim data satuan ke view edit
     *
     * Parameter: string $id (ID satuan yang akan diedit)
     * Return: View form edit dengan data satuan yang sudah ada
     * Use case: Admin ingin mengubah nama satuan (misal: "pcs" jadi "pieces")
     */
    public function edit(string $id)
    {
        // Cari satuan berdasarkan ID, jika tidak ada akan error 404
        $satuan = Satuans::findOrFail($id);

        // Tampilkan form edit dengan data satuan yang sudah ada
        return view('satuan.edit', compact('satuan'));
    }

    /**
     * update()
     * Fungsi: Memperbarui data satuan yang sudah ada
     * Flow:
     * 1. Validasi input nama_satuan
     * 2. Cari satuan berdasarkan ID
     * 3. Update data satuan di database
     * 4. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter:
     * - Request $request (data form yang sudah divalidasi)
     * - string $id (ID satuan yang akan diupdate)
     * Return: Redirect ke satuan.index dengan flash message
     * Note: Update akan otomatis mempengaruhi semua produk yang pakai satuan ini
     */
    public function update(Request $request, string $id)
    {
        // Validasi input dari form edit
        $request->validate([
            'nama_satuan' => 'required|string|max:50',
        ]);

        // Cari satuan berdasarkan ID
        $satuan = Satuans::findOrFail($id);

        // Update data satuan dengan data baru dari form
        $satuan->update($request->all());

        // Redirect ke halaman daftar satuan dengan pesan sukses
        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * destroy()
     * Fungsi: Menghapus satuan dari database
     * Flow:
     * 1. Cari satuan berdasarkan ID
     * 2. Hapus satuan dari database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: string $id (ID satuan yang akan dihapus)
     * Return: Redirect ke satuan.index dengan flash message
     * Warning: HATI-HATI! Jika satuan masih digunakan oleh produk,
     *          akan menyebabkan error karena foreign key constraint
     * Best practice: Cek dulu apakah satuan masih digunakan produk sebelum hapus
     */
    public function destroy(string $id)
    {
        // Cari satuan berdasarkan ID
        $satuan = Satuans::findOrFail($id);

        // Hapus satuan dari database
        $satuan->delete();

        // Redirect ke halaman daftar satuan dengan pesan sukses
        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
