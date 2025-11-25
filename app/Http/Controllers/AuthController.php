<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;

/**
 * AuthController
 * Controller untuk menangani proses autentikasi (login, register, logout)
 */
class AuthController extends Controller
{
    /**
     * showLoginForm()
     * Fungsi: Menampilkan halaman form login
     * Return: View login (auth.login)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * login()
     * Fungsi: Memproses login user dengan validasi email, password, dan reCAPTCHA
     * Flow:
     * 1. Validasi input (email, sandi, reCAPTCHA)
     * 2. Cek kredensial user ke database
     * 3. Jika valid, regenerasi session untuk keamanan
     * 4. Redirect berdasarkan role:
     *    - Admin → /admindashboard
     *    - Kasir → /penjualan/create
     * 5. Jika gagal, kembali ke form login dengan error
     */
    public function login(Request $request)
    {
        // Validasi input user
        $request->validate([
            'email' => 'required|email',
            'sandi' => 'required',
            'g-recaptcha-response' => 'required|captcha', // Validasi reCAPTCHA
        ], [
            'g-recaptcha-response.required' => 'Mohon verifikasi bahwa Anda bukan robot.',
            'g-recaptcha-response.captcha' => 'Verifikasi reCAPTCHA gagal, coba lagi.',
        ]);

        // Mapping kredensial (karena field password di database bernama 'sandi')
        $credentials = [
            'email' => $request->email,
            'password' => $request->sandi, // Laravel Auth expects 'password' key
        ];

        // Cek kredensial user menggunakan Auth facade
        if (Auth::attempt($credentials)) {
            // Regenerasi session untuk mencegah session fixation attack
            $request->session()->regenerate();

            // Ambil data user yang berhasil login
            $user = Auth::user();

            // Routing berdasarkan role user
            if ($user->role === 'admin') {
                return redirect()->intended('/admindashboard')->with('success', 'Login berhasil sebagai admin!');
            } elseif ($user->role === 'kasir') {
                return redirect()->intended('/penjualan/create')->with('success', 'Login berhasil sebagai kasir!');
            } else {
                // Jika role tidak valid, logout paksa dan kembalikan error
                Auth::logout();
                return back()->withErrors(['email' => 'Role pengguna tidak dikenali.'])->withInput();
            }
        }

        // Jika kredensial salah, kembali ke form dengan error
        return back()->withErrors([
            'email' => 'Email atau sandi salah.',
        ])->withInput($request->only('email')); // Pertahankan input email
    }

    /**
     * showRegisterForm()
     * Fungsi: Menampilkan halaman form registrasi
     * Return: View register (auth.register)
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * register()
     * Fungsi: Memproses registrasi user baru
     * Flow:
     * 1. Validasi semua input (name, email unique, no_telp, sandi, reCAPTCHA)
     * 2. Buat user baru di database dengan role default 'admin'
     * 3. Hash password menggunakan bcrypt
     * 4. Auto-login setelah registrasi berhasil
     * 5. Redirect ke dashboard admin
     */
    public function register(Request $request)
    {
        // Validasi input registrasi
        $request->validate([
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|unique:tbl_users,email', // Email harus unik di tabel tbl_users
            'no_telp'=> 'required|string|max:15',
            'alamat' => 'nullable|string', // Alamat boleh kosong
            'sandi'  => 'required|min:8|confirmed', // Password min 8 karakter dan harus ada konfirmasi
            'g-recaptcha-response' => 'required|captcha',
        ], [
            'g-recaptcha-response.required' => 'Mohon verifikasi bahwa Anda bukan robot.',
            'g-recaptcha-response.captcha' => 'Verifikasi reCAPTCHA gagal, coba lagi.',
        ]);

        // Insert data user baru ke database
        $user = Users::create([
            'name'   => $request->name,
            'email'  => $request->email,
            'no_telp'=> $request->no_telp,
            'alamat' => $request->alamat,
            'sandi'  => Hash::make($request->sandi), // Encrypt password dengan bcrypt
            'role'   => 'admin', // Default role adalah admin
        ]);

        // Auto-login user setelah registrasi
        Auth::login($user);

        // Redirect ke dashboard admin dengan pesan sukses
        return redirect('/admindashboard')->with('success', 'Registrasi berhasil, selamat datang!');
    }

    /**
     * logout()
     * Fungsi: Proses logout user
     * Flow:
     * 1. Logout user dari sistem
     * 2. Invalidate session saat ini
     * 3. Regenerate CSRF token untuk keamanan
     * 4. Redirect ke halaman login
     */
    public function logout(Request $request)
    {
        // Logout user
        Auth::logout();

        // Hapus semua data session
        $request->session()->invalidate();

        // Generate token CSRF baru untuk mencegah CSRF attack
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan sukses
        return redirect('/login')->with('success', 'Anda sudah logout.');
    }
}
