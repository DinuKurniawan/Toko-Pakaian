<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan import Facade Auth ini

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Gunakan Auth::check() dan Auth::user() agar Intelephense bisa membacanya
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak. Anda bukan Admin.');
        }

        return $next($request);
    }
}
