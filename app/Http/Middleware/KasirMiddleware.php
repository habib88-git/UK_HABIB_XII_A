<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasirMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (Auth::user()->role !== 'kasir') {
            return redirect('/admindashboard')->with('error', 'Akses ditolak, hanya kasir yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}
