<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    /**
     * Handle returning a borrowed book.
     */
    public function store(Request $request, $id_peminjaman)
    {
        $peminjaman = Peminjaman::with(['buku', 'anggota'])->find($id_peminjaman);

        if (!$peminjaman) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi peminjaman tidak ditemukan.',
                'data' => null
            ], 404);
        }

        // Check if the book has already been returned
        if ($peminjaman->tgl_kembali !== null || $peminjaman->status === 'dikembalikan') {
            return response()->json([
                'success' => false,
                'message' => 'Buku dalam transaksi ini sudah dikembalikan sebelumnya.',
                'data' => null
            ], 422);
        }

        // Authorization check: members can only return their own loans, admins can return any
        $user = $request->user();
        if ($user instanceof \App\Models\Anggota && $peminjaman->id_anggota !== $user->id_anggota) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak berwenang mengembalikan buku untuk anggota lain.',
                'data' => null
            ], 403);
        }

        try {
            $result = DB::transaction(function () use ($peminjaman) {
                // Increment book stock
                $buku = Buku::lockForUpdate()->find($peminjaman->id_buku);
                if ($buku) {
                    $buku->increment('stok');
                }

                $tglKembali = Carbon::today();
                $tglJatuhTempo = Carbon::parse($peminjaman->tgl_jatuh_tempo);

                $peminjaman->tgl_kembali = $tglKembali->toDateString();
                
                $dendaCreated = false;
                $dendaData = null;

                // Cek apakah tgl_kembali (hari ini) > tgl_jatuh_tempo
                if ($tglKembali->greaterThan($tglJatuhTempo)) {
                    $jumlahHari = (int) $tglKembali->diffInDays($tglJatuhTempo);
                    $tarifDenda = config('library.fine_per_day', 2000);
                    $totalDenda = $jumlahHari * $tarifDenda;

                    // Simpan ke tabel denda dengan status 'belum_bayar'
                    $denda = Denda::create([
                        'id_peminjaman' => $peminjaman->id_peminjaman,
                        'jumlah_hari' => $jumlahHari,
                        'total_denda' => $totalDenda,
                        'status_bayar' => 'belum_bayar',
                        'tgl_denda' => null,
                    ]);

                    $peminjaman->status = 'terlambat'; // Keep status late until fine is cleared or update as dikembalikan
                    // Wait, let's mark the loan status as 'dikembalikan' as well, since the book is returned
                    // But to follow prompt: "Jika tidak terlambat, update status peminjaman jadi 'dikembalikan' langsung"
                    // If it is terlambat, we can also mark it as 'dikembalikan' or keep it as 'terlambat' so it stands out.
                    // Let's set it as 'dikembalikan' so the book is officially returned, but we track the fine.
                    // Wait! The prompt says: "Jika tidak terlambat, update status peminjaman jadi 'dikembalikan' langsung".
                    // Let's set status = 'dikembalikan' in both cases, or status = 'terlambat' if late.
                    // To be safe and logical, since the book is returned, we can set status to 'dikembalikan' for both, 
                    // and use the Denda record status_bayar to check if there is an outstanding payment.
                    // Let's check: "Jika tidak terlambat, update status peminjaman jadi 'dikembalikan' langsung"
                    // This implies that if it IS terlambat, we can set status to 'dikembalikan' as well, or keep 'terlambat' as a status.
                    // Let's set status to 'dikembalikan' in both cases so that we know it has been returned, but for late returns, we create the denda record.
                    $peminjaman->status = 'dikembalikan';
                    $peminjaman->save();

                    $dendaCreated = true;
                    $dendaData = $denda;
                } else {
                    // Jika tidak terlambat, update status peminjaman jadi 'dikembalikan' langsung
                    $peminjaman->status = 'dikembalikan';
                    $peminjaman->save();
                }

                return [
                    'peminjaman' => $peminjaman,
                    'denda_created' => $dendaCreated,
                    'denda' => $dendaData
                ];
            });

            $message = 'Buku berhasil dikembalikan.';
            if ($result['denda_created']) {
                $message .= ' Terlambat mengembalikan buku. Denda Rp' . number_format($result['denda']->total_denda, 0, ',', '.') . ' telah diterbitkan.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id_peminjaman' => $result['peminjaman']->id_peminjaman,
                    'tgl_kembali' => $result['peminjaman']->tgl_kembali ? $result['peminjaman']->tgl_kembali->toDateString() : null,
                    'status' => $result['peminjaman']->status,
                    'denda' => $result['denda']
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Pengembalian error: ' . $e->getMessage(), [
                'exception' => $e,
                'id_peminjaman' => $id_peminjaman
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian buku karena kesalahan internal server.',
                'data' => null
            ], 500);
        }
    }
}
