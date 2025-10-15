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
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->sandi,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admindashboard')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'email' => 'Email atau sandi salah.',
        ]);
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
            'sandi'  => 'required|min:6|confirmed', // pakai field sandi_confirmation
        ]);

        $user = Users::create([
            'name'   => $request->name,
            'email'  => $request->email,
            'no_telp'=> $request->no_telp,
            'alamat' => $request->alamat,
            'sandi'  => Hash::make($request->sandi),
            'role'   => 'admin', // default role
        ]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registrasi berhasil, selamat datang!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda sudah logout.');
    }
}
