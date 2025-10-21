<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Users;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'sandi' => 'required',
        'g-recaptcha-response' => 'required|captcha',
    ], [
        'g-recaptcha-response.required' => 'Mohon verifikasi bahwa Anda bukan robot.',
        'g-recaptcha-response.captcha' => 'Verifikasi reCAPTCHA gagal, coba lagi.',
    ]);

    $credentials = [
        'email' => $request->email,
        'password' => $request->sandi,
    ];

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Ambil user yang login
        $user = Auth::user();

        // Cek role user
        if ($user->role === 'admin') {
            return redirect()->intended('/admindashboard')->with('success', 'Login berhasil sebagai admin!');
        } elseif ($user->role === 'kasir') {
            return redirect()->intended('/penjualan/create')->with('success', 'Login berhasil sebagai kasir!');
        } else {
            // Jika role tidak dikenali
            Auth::logout();
            return back()->withErrors(['email' => 'Role pengguna tidak dikenali.'])->withInput();
        }
    }

    return back()->withErrors([
        'email' => 'Email atau sandi salah.',
    ])->withInput($request->only('email'));
}


    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
        'name'   => 'required|string|max:100',
        'email'  => 'required|email|unique:tbl_users,email',
        'no_telp'=> 'required|string|max:15',
        'alamat' => 'nullable|string',
        'sandi'  => 'required|min:8|confirmed',
        'g-recaptcha-response' => 'required|captcha',
    ], [
        'g-recaptcha-response.required' => 'Mohon verifikasi bahwa Anda bukan robot.',
        'g-recaptcha-response.captcha' => 'Verifikasi reCAPTCHA gagal, coba lagi.',
    ]);

    $user = Users::create([
        'name'   => $request->name,
        'email'  => $request->email,
        'no_telp'=> $request->no_telp,
        'alamat' => $request->alamat,
        'sandi'  => Hash::make($request->sandi),
        'role'   => 'admin',
    ]);

    // login langsung setelah registrasi
    Auth::login($user);

    return redirect('/admindashboard')->with('success', 'Registrasi berhasil, selamat datang!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda sudah logout.');
    }
}
