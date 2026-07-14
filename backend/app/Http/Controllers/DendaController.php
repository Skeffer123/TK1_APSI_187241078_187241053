<?php

namespace App\Http\Controllers;

use App\Models\Denda;
use Illuminate\Http\Request;

class DendaController extends Controller
{
    /**
     * Mark a fine as paid/settled (Admin only).
     */
    public function bayar(Request $request, $id)
    {
        $denda = Denda::with('peminjaman.anggota', 'peminjaman.buku')->find($id);

        if (!$denda) {
            return response()->json([
                'success' => false,
                'message' => 'Data denda tidak ditemukan.',
                'data' => null
            ], 404);
        }

        if ($denda->status_bayar === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Denda ini sudah lunas dibayar.',
                'data' => $denda
            ], 422);
        }

        $denda->status_bayar = 'lunas';
        $denda->tgl_denda = date('Y-m-d');
        $denda->save();

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran denda berhasil dicatat. Status denda Lunas.',
            'data' => $denda
        ]);
    }
}
