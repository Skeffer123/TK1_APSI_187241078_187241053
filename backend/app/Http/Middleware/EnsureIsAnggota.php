<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Anggota;

class EnsureIsAnggota
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user() instanceof Anggota) {
            if ($request->user()->status !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anggota Anda dinonaktifkan. Silakan hubungi admin.',
                    'data' => null
                ], 403);
            }
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak. Anda bukan Anggota.',
            'data' => null
        ], 403);
    }
}
