<?php

namespace App\Http\Controllers;

use App\Models\Kategoris;
use Illuminate\Http\Request;

/**
 * kategoriController
 * Controller untuk mengelola data kategori produk (CRUD)
 * Menggunakan Resource Controller pattern dari Laravel
 */
class kategoriController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua kategori
     * Flow:
     * 1. Ambil semua data kategori dari database
     * 2. Kirim data ke view kategori.index
     * Return: View dengan daftar kategori
     */
    public function index()
    {
        // Ambil semua data kategori dari tabel kategoris
        $kategoris = Kategoris::all();

        // Tampilkan view index dengan data kategori
        return view('kategori.index', compact('kategoris'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form untuk membuat kategori baru
     * Return: View form tambah kategori
     */
    public function create()
    {
        // Tampilkan form create kategori
        return view('kategori.create');
    }

    /**
     * store()
     * Fungsi: Menyimpan kategori baru ke database
     * Flow:
     * 1. Validasi input nama_kategori (wajib diisi, max 100 karakter)
     * 2. Simpan data kategori ke database
     * 3. Redirect ke halaman index dengan pesan sukses
     * Parameter: Request $request (berisi data dari form)
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'nama_kategori' => 'required|string|max:100', // Nama kategori wajib, string, max 100 char
        ]);

        // Insert data kategori baru ke database
        Kategoris::create($request->all());

        // Redirect ke halaman daftar kategori dengan flash message sukses
        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * show()
     * Fungsi: Menampilkan detail kategori tertentu (tidak digunakan)
     * Parameter: string $id (ID kategori)
     * Note: Fungsi ini kosong, kemungkinan tidak diperlukan dalam sistem ini
     */
    public function show(string $id)
    {
        // Fungsi show tidak diimplementasikan
        // Biasanya digunakan untuk menampilkan detail 1 kategori
    }

    /**
     * edit()
     * Fungsi: Menampilkan form untuk edit kategori
     * Flow:
     * 1. Cari kategori berdasarkan ID
     * 2. Jika tidak ditemukan, akan throw 404 error
     * 3. Kirim data kategori ke view edit
     * Parameter: string $id (ID kategori yang akan diedit)
     * Return: View form edit dengan data kategori
     */
    public function edit(string $id)
    {
        // Cari kategori berdasarkan ID, jika tidak ada akan error 404
        $kategori = Kategoris::findOrFail($id);

        // Tampilkan form edit dengan data kategori yang sudah ada
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * update()
     * Fungsi: Memperbarui data kategori yang sudah ada
     * Flow:
     * 1. Validasi input nama_kategori
     * 2. Cari kategori berdasarkan ID
     * 3. Update data kategori di database
     * 4. Redirect ke halaman index dengan pesan sukses
     * Parameter:
     * - Request $request (data form yang sudah divalidasi)
     * - string $id (ID kategori yang akan diupdate)
     */
    public function update(Request $request, string $id)
    {
        // Validasi input dari form edit
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);

        // Cari kategori berdasarkan ID
        $kategori = Kategoris::findOrFail($id);

        // Update data kategori dengan data baru dari form
        $kategori->update($request->all());

        // Redirect ke halaman daftar kategori dengan pesan sukses
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * destroy()
     * Fungsi: Menghapus kategori dari database
     * Flow:
     * 1. Cari kategori berdasarkan ID
     * 2. Hapus kategori dari database (soft delete jika diaktifkan)
     * 3. Redirect ke halaman index dengan pesan sukses
     * Parameter: string $id (ID kategori yang akan dihapus)
     * Warning: Pastikan tidak ada produk yang masih menggunakan kategori ini
     */
    public function destroy(string $id)
    {
        // Cari kategori berdasarkan ID
        $kategori = Kategoris::findOrFail($id);

        // Hapus kategori dari database
        $kategori->delete();

        // Redirect ke halaman daftar kategori dengan pesan sukses
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
