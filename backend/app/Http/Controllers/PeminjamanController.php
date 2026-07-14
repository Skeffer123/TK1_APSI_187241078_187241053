<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    /**
     * Display all loan transactions (Admin only).
     */
    public function index(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'buku', 'denda']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter date range
        if ($request->filled('tgl_awal') && $request->filled('tgl_akhir')) {
            $query->whereBetween('tgl_pinjam', [$request->input('tgl_awal'), $request->input('tgl_akhir')]);
        }

        $perPage = $request->input('per_page', 10);
        $peminjaman = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi peminjaman berhasil diambil.',
            'data' => $peminjaman
        ]);
    }

    /**
     * Display active loans for the logged-in member (Anggota only).
     */
    public function status(Request $request)
    {
        $anggota = $request->user();

        // Get all loans of this member, including book details and fines
        $loans = Peminjaman::with(['buku', 'denda'])
            ->where('id_anggota', $anggota->id_anggota)
            ->orderBy('tgl_pinjam', 'desc')
            ->get();

        // Calculate runtime denda for currently late books that haven't been returned yet
        $today = Carbon::today();
        $loans = $loans->map(function ($loan) use ($today) {
            $loanData = $loan->toArray();
            
            // If the book is still borrowed and past due, calculate the estimated denda
            if ($loan->status === 'dipinjam' || $loan->status === 'terlambat') {
                $dueDate = Carbon::parse($loan->tgl_jatuh_tempo);
                if ($today->greaterThan($dueDate)) {
                    $lateDays = $today->diffInDays($dueDate);
                    $rate = config('library.fine_per_day', 2000);
                    $estimatedFine = $lateDays * $rate;
                    
                    $loanData['runtime_denda'] = [
                        'jumlah_hari' => $lateDays,
                        'total_denda' => $estimatedFine,
                        'status_bayar' => 'belum_bayar',
                        'message' => 'Estimasi denda keterlambatan saat ini.'
                    ];

                    // Auto-update status to 'terlambat' in db if it's currently 'dipinjam'
                    if ($loan->status === 'dipinjam') {
                        $loan->status = 'terlambat';
                        $loan->save();
                        $loanData['status'] = 'terlambat';
                    }
                }
            }
            return $loanData;
        });

        return response()->json([
            'success' => true,
            'message' => 'Status peminjaman anggota berhasil diambil.',
            'data' => $loans
        ]);
    }

    /**
     * Borrow a book (Anggota only).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_buku' => 'required|exists:buku,id_buku',
        ], [
            'id_buku.required' => 'Buku wajib dipilih.',
            'id_buku.exists' => 'Buku tidak ditemukan di sistem.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $anggota = $request->user();
        $idBuku = $request->input('id_buku');

        // 1. Validasi status anggota aktif (tolak jika nonaktif)
        if ($anggota->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda berstatus nonaktif. Anda tidak dapat melakukan peminjaman buku.',
                'data' => null
            ], 403);
        }

        // Prevent borrowing if the member already has the SAME book actively borrowed
        $alreadyBorrowed = Peminjaman::where('id_anggota', $anggota->id_anggota)
            ->where('id_buku', $idBuku)
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->exists();

        if ($alreadyBorrowed) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih meminjam buku ini. Silakan kembalikan terlebih dahulu.',
                'data' => null
            ], 422);
        }

        try {
            $loan = DB::transaction(function () use ($anggota, $idBuku) {
                // Find and lock book row for update to prevent race conditions
                $buku = Buku::lockForUpdate()->find($idBuku);

                // 2. Cek stok buku tersedia (tolak jika stok 0)
                if ($buku->stok <= 0) {
                    throw new \Exception('Stok buku ini habis. Tidak dapat meminjam.', 422);
                }

                // 3. Jika lolos:
                // Kurangi stok buku sebanyak 1
                $buku->decrement('stok');

                // Generate tgl_pinjam (hari ini) dan tgl_jatuh_tempo (+7 hari)
                $tglPinjam = Carbon::today();
                $borrowDays = config('library.borrow_days', 7);
                $tglJatuhTempo = $tglPinjam->copy()->addDays($borrowDays);

                // Catat transaksi
                return Peminjaman::create([
                    'id_anggota' => $anggota->id_anggota,
                    'id_buku' => $buku->id_buku,
                    'tgl_pinjam' => $tglPinjam->toDateString(),
                    'tgl_jatuh_tempo' => $tglJatuhTempo->toDateString(),
                    'status' => 'dipinjam',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman buku berhasil dicatat.',
                'data' => $loan
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Peminjaman error: ' . $e->getMessage(), [
                'exception' => $e,
                'user' => $anggota ? $anggota->id_anggota : null,
                'id_buku' => $idBuku
            ]);

            $message = $e->getCode() === 422 
                ? $e->getMessage() 
                : 'Gagal memproses peminjaman buku karena kesalahan internal server.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null
            ], $e->getCode() === 422 ? 422 : 500);
        }
    }
}
