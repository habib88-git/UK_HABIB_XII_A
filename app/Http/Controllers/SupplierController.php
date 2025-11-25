<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suppliers;

/**
 * SupplierController
 * Controller untuk mengelola data supplier/pemasok produk (CRUD)
 * Supplier: Pihak yang memasok produk ke toko/bisnis
 * Menggunakan Resource Controller pattern dari Laravel
 */
class SupplierController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua supplier
     * Flow:
     * 1. Ambil semua data supplier dari database
     * 2. Kirim data ke view supplier.index
     *
     * Return: View dengan daftar supplier
     * Use case: Admin melihat daftar supplier yang bekerja sama dengan toko
     * Info: Berisi nama supplier, alamat, no telepon
     */
    public function index()
    {
        // Ambil semua data supplier
        $suppliers = Suppliers::all();

        // Tampilkan view index dengan data supplier
        return view('supplier.index', compact('suppliers'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form untuk menambah supplier baru
     * Flow:
     * 1. Tampilkan form input data supplier baru
     *
     * Return: View form tambah supplier
     * Use case: Admin ingin menambah supplier baru (PT Indofood, Unilever, dll)
     * Note: Form berisi input nama_supplier, alamat, no_telp
     */
    public function create()
    {
        // Tampilkan form create supplier
        return view('supplier.create');
    }

    /**
     * store()
     * Fungsi: Menyimpan data supplier baru ke database
     * Flow:
     * 1. Validasi input:
     *    - nama_supplier: wajib, max 100 karakter
     *    - alamat: wajib diisi (alamat lengkap supplier)
     *    - no_telp: wajib, min 10 digit, max 12 digit
     * 2. Simpan data supplier ke database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: Request $request (berisi data dari form)
     * Return: Redirect ke supplier.index dengan flash message
     * Contoh data:
     *   - nama_supplier: "PT Indofood Sukses Makmur"
     *   - alamat: "Jl. Sudirman No.123, Jakarta"
     *   - no_telp: "0812345678901"
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'nama_supplier' => 'required|max:100',     // Nama supplier wajib, max 100 char
            'alamat'        => 'required',              // Alamat lengkap wajib diisi
            'no_telp'       => 'required|min:10|max:12', // No telepon wajib, 10-12 digit
        ]);

        // Insert data supplier baru ke database
        Suppliers::create($request->all());

        // Redirect ke halaman daftar supplier dengan flash message sukses
        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil ditambahkan.');
    }

    /**
     * show()
     * Fungsi: Menampilkan detail supplier tertentu (tidak digunakan)
     * Parameter: string $id (ID supplier)
     * Note: Fungsi ini kosong karena tidak ada kebutuhan halaman detail supplier
     *       Data supplier sudah cukup ditampilkan di list (nama, alamat, no telp)
     *       Jika perlu detail lebih lanjut bisa menambahkan fitur:
     *       - List produk dari supplier ini
     *       - History pembelian dari supplier ini
     */
    public function show(string $id)
    {
        // Fungsi show tidak diimplementasikan
        // Bisa dikembangkan untuk menampilkan:
        // - Detail supplier
        // - Daftar produk yang disupply
        // - History transaksi pembelian
    }

    /**
     * edit()
     * Fungsi: Menampilkan form untuk edit data supplier
     * Flow:
     * 1. Cari supplier berdasarkan ID
     * 2. Jika tidak ditemukan, akan throw 404 error
     * 3. Kirim data supplier ke view edit
     *
     * Parameter: string $id (ID supplier yang akan diedit)
     * Return: View form edit dengan data supplier yang sudah ada
     * Use case: Admin ingin mengubah data supplier (ganti alamat, no telepon, dll)
     */
    public function edit(string $id)
    {
        // Cari supplier berdasarkan ID, jika tidak ada akan error 404
        $supplier = Suppliers::findOrFail($id);

        // Tampilkan form edit dengan data supplier yang sudah ada
        return view('supplier.edit', compact('supplier'));
    }

    /**
     * update()
     * Fungsi: Memperbarui data supplier yang sudah ada
     * Flow:
     * 1. Cari supplier berdasarkan ID
     * 2. Update data supplier di database dengan data baru dari form
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter:
     * - Request $request (data form yang akan diupdate)
     * - string $id (ID supplier yang akan diupdate)
     * Return: Redirect ke supplier.index dengan flash message
     * Note: Tidak ada validasi di method ini (seharusnya ada untuk keamanan)
     *       Best practice: tambahkan validasi seperti di store()
     * Warning: Update supplier akan otomatis update relasi di produk/pembelian
     */
    public function update(Request $request, string $id)
    {
        // Cari supplier berdasarkan ID
        $supplier = Suppliers::findOrFail($id);

        // Update data supplier dengan data baru dari form
        $supplier->update($request->all());

        // Redirect ke halaman daftar supplier dengan pesan sukses
        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil diperbarui.');
    }

    /**
     * destroy()
     * Fungsi: Menghapus data supplier dari database
     * Flow:
     * 1. Cari supplier berdasarkan ID
     * 2. Hapus supplier dari database
     * 3. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: string $id (ID supplier yang akan dihapus)
     * Return: Redirect ke supplier.index dengan flash message
     * Warning: HATI-HATI! Jika supplier masih digunakan oleh produk/pembelian,
     *          akan menyebabkan error karena foreign key constraint
     * Best practice:
     *   - Cek dulu apakah supplier masih punya produk atau pembelian
     *   - Atau gunakan soft delete agar data tetap ada di database
     */
    public function destroy(string $id)
    {
        // Cari supplier berdasarkan ID
        $supplier = Suppliers::findOrFail($id);

        // Hapus supplier dari database
        $supplier->delete();

        // Redirect ke halaman daftar supplier dengan pesan sukses
        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil dihapus.');
    }
}
