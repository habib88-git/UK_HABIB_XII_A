<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;

/**
 * UserController
 * Controller untuk mengelola data user/pengguna sistem (CRUD)
 * User: Pengguna sistem POS dengan role admin atau kasir
 * Fitur: Manajemen user, role-based access, password encryption
 */
class UserController extends Controller
{
    /**
     * index()
     * Fungsi: Menampilkan daftar semua user
     * Flow:
     * 1. Ambil semua data user dari database
     * 2. Urutkan berdasarkan user_id descending (terbaru di atas)
     * 3. Kirim data ke view users.index
     *
     * Return: View dengan daftar user
     * Use case: Admin melihat daftar user yang terdaftar di sistem
     * Info tampil: nama, email, role (admin/kasir), no telp, alamat
     * Note: Urutkan DESC agar user baru tampil paling atas
     */
    public function index()
    {
        // Ambil semua user, urutkan dari user_id terbesar (user baru di atas)
        $users = Users::orderBy('user_id', 'desc')->get();

        // Tampilkan view index dengan data users
        return view('users.index', compact('users'));
    }

    /**
     * create()
     * Fungsi: Menampilkan form untuk menambah user baru
     * Flow:
     * 1. Tampilkan form input data user baru
     *
     * Return: View form tambah user
     * Use case: Admin ingin menambah user baru (kasir atau admin lain)
     * Note: Form berisi input: name, email, password, no_telp, alamat, role
     */
    public function create()
    {
        // Tampilkan form create user
        return view('users.create');
    }

    /**
     * store()
     * Fungsi: Menyimpan user baru ke database dengan password terenkripsi
     * Flow:
     * 1. Validasi input:
     *    - name: wajib, max 100 karakter
     *    - email: wajib, format email, unique (tidak boleh duplikat)
     *    - sandi: wajib, minimal 8 karakter
     *    - no_telp: opsional, minimal 12 digit
     *    - alamat: opsional
     *    - role: wajib, hanya boleh admin atau kasir
     * 2. Encrypt password menggunakan bcrypt
     * 3. Simpan user baru ke database
     * 4. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: Request $request (berisi data dari form)
     * Return: Redirect ke users.index dengan flash message
     * Security: Password di-hash dengan bcrypt sebelum disimpan
     * Note: Email harus unique untuk mencegah duplikasi akun
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name'      => 'required|string|max:100',              // Nama wajib, max 100 char
            'email'     => 'required|email|unique:tbl_users,email', // Email wajib, unique
            'sandi'     => 'required|min:8',                       // Password wajib, min 8 char
            'no_telp'   => 'nullable|string|min:12',               // No telp opsional, min 12 digit
            'alamat'    => 'nullable|string',                      // Alamat opsional
            'role'      => 'required|in:admin,kasir',              // Role wajib: admin/kasir
        ]);

        // Insert user baru dengan password terenkripsi
        Users::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'sandi'     => bcrypt($request->sandi),  // ✅ Encrypt password dengan bcrypt
            'no_telp'   => $request->no_telp,
            'alamat'    => $request->alamat,
            'role'      => $request->role,
        ]);

        // Redirect ke halaman daftar user dengan flash message sukses
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    /**
     * show()
     * Fungsi: Menampilkan detail user tertentu (tidak digunakan)
     * Parameter: string $id (ID user)
     * Note: Fungsi ini kosong, bisa dikembangkan untuk menampilkan:
     *       - Profile user lengkap
     *       - History aktivitas user (login, transaksi)
     *       - Statistik transaksi (untuk kasir)
     */
    public function show(string $id)
    {
        // Fungsi show tidak diimplementasikan
        // Bisa dikembangkan untuk menampilkan:
        // - Detail profile user
        // - History login user
        // - Transaksi yang dilakukan user (jika kasir)
    }

    /**
     * edit()
     * Fungsi: Menampilkan form untuk edit data user
     * Flow:
     * 1. Cari user berdasarkan ID
     * 2. Jika tidak ditemukan, akan throw 404 error
     * 3. Kirim data user ke view edit
     *
     * Parameter: string $id (ID user yang akan diedit)
     * Return: View form edit dengan data user yang sudah ada
     * Use case: Admin ingin mengubah data user (ganti role, email, dll)
     * Note: Password tidak ditampilkan di form edit (security)
     */
    public function edit(string $id)
    {
        // Cari user berdasarkan ID, jika tidak ada akan error 404
        $user = Users::findOrFail($id);

        // Tampilkan form edit dengan data user yang sudah ada
        return view('users.edit', compact('user'));
    }

    /**
     * update()
     * Fungsi: Memperbarui data user yang sudah ada
     * Flow:
     * 1. Cari user berdasarkan ID
     * 2. Validasi input (email unique kecuali untuk user ini sendiri)
     * 3. Siapkan data yang akan diupdate
     * 4. Jika password diisi, encrypt dan update password
     * 5. Jika password kosong, tidak update password (tetap pakai password lama)
     * 6. Update data user di database
     * 7. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter:
     * - Request $request (data form yang akan diupdate)
     * - string $id (ID user yang akan diupdate)
     * Return: Redirect ke users.index dengan flash message
     * Security:
     * - Password opsional saat update (nullable)
     * - Email unique kecuali untuk user ini sendiri
     * - Password di-hash jika diisi
     * Note: Password lama tetap aman jika field password tidak diisi
     */
    public function update(Request $request, string $id)
    {
        // Cari user berdasarkan ID
        $user = Users::findOrFail($id);

        // Validasi input dari form edit
        $request->validate([
            'name'      => 'required|string|max:100',
            // Email unique kecuali untuk user ini sendiri (ignore current user)
            'email'     => 'required|email|unique:tbl_users,email,' . $id . ',user_id',
            'sandi'     => 'nullable|min:8', // Password opsional saat update
            'no_telp'   => 'nullable|string|min:12',
            'alamat'    => 'nullable|string',
            'role'      => 'required|in:admin,kasir',
        ]);

        // Siapkan data yang akan diupdate (tanpa password dulu)
        $updateData = [
            'name'      => $request->name,
            'email'     => $request->email,
            'no_telp'   => $request->no_telp,
            'alamat'    => $request->alamat,
            'role'      => $request->role,
        ];

        // ✅ Jika password diisi, encrypt dan tambahkan ke updateData
        // Jika password kosong, tidak update password (tetap pakai password lama)
        if ($request->filled('sandi')) {
            $updateData['sandi'] = bcrypt($request->sandi);
        }

        // Update data user dengan data baru
        $user->update($updateData);

        // Redirect ke halaman daftar user dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    /**
     * destroy()
     * Fungsi: Menghapus user dari database
     * Flow:
     * 1. Hapus user berdasarkan user_id
     * 2. Redirect ke halaman index dengan pesan sukses
     *
     * Parameter: string $id (ID user yang akan dihapus)
     * Return: Redirect ke users.index dengan flash message
     * Warning: HATI-HATI saat hapus user!
     *   - Jika user masih punya transaksi, bisa error foreign key
     *   - Jangan hapus user yang sedang login
     *   - History transaksi akan kehilangan referensi user
     * Best practice:
     *   - Cek dulu apakah user punya transaksi
     *   - Atau gunakan soft delete
     *   - Atau set status inactive daripada hapus
     */
    public function destroy(string $id)
    {
        // Hapus user berdasarkan user_id
        Users::where('user_id', $id)->delete();

        // Redirect ke halaman daftar user dengan pesan sukses
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
