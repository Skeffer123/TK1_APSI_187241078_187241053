<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Anggota;
use App\Models\Peminjaman;
use App\Models\Denda;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Get data helper based on report type and date filters.
     */
    private function getReportData($jenis, $periodeAwal = null, $periodeAkhir = null)
    {
        $data = [];
        switch ($jenis) {
            case 'buku':
                $query = Buku::with('admin');
                if ($periodeAwal && $periodeAkhir) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($periodeAwal)->startOfDay(),
                        Carbon::parse($periodeAkhir)->endOfDay()
                    ]);
                }
                $data = $query->orderBy('judul', 'asc')->get();
                break;

            case 'anggota':
                $query = Anggota::with('admin');
                if ($periodeAwal && $periodeAkhir) {
                    $query->whereBetween('tanggal_daftar', [$periodeAwal, $periodeAkhir]);
                }
                $data = $query->orderBy('nama', 'asc')->get();
                break;

            case 'peminjaman':
                $query = Peminjaman::with(['anggota', 'buku']);
                if ($periodeAwal && $periodeAkhir) {
                    $query->whereBetween('tgl_pinjam', [$periodeAwal, $periodeAkhir]);
                }
                $data = $query->orderBy('tgl_pinjam', 'desc')->get();
                break;

            case 'denda':
                $query = Denda::with(['peminjaman.anggota', 'peminjaman.buku']);
                if ($periodeAwal && $periodeAkhir) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($periodeAwal)->startOfDay(),
                        Carbon::parse($periodeAkhir)->endOfDay()
                    ]);
                }
                $data = $query->orderBy('created_at', 'desc')->get();
                break;
        }

        return $data;
    }

    /**
     * Get JSON preview of a report (and log it).
     */
    public function generateReport(Request $request, $jenis)
    {
        if (!in_array($jenis, ['buku', 'anggota', 'peminjaman', 'denda'])) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis laporan tidak valid. Gunakan: buku, anggota, peminjaman, atau denda.',
                'data' => null
            ], 422);
        }

        $periodeAwal = $request->query('periode_awal');
        $periodeAkhir = $request->query('periode_akhir');

        // Log generation to database
        Laporan::create([
            'id_admin' => $request->user()->id_admin,
            'jenis_laporan' => $jenis,
            'tgl_generate' => Carbon::now(),
            'periode_awal' => $periodeAwal ?: null,
            'periode_akhir' => $periodeAkhir ?: null,
        ]);

        $reportData = $this->getReportData($jenis, $periodeAwal, $periodeAkhir);

        return response()->json([
            'success' => true,
            'message' => 'Laporan ' . ucfirst($jenis) . ' berhasil digenerate.',
            'data' => [
                'jenis' => $jenis,
                'periode_awal' => $periodeAwal,
                'periode_akhir' => $periodeAkhir,
                'total_records' => count($reportData),
                'items' => $reportData
            ]
        ]);
    }

    /**
     * Export report to PDF.
     */
    public function exportPdf(Request $request, $jenis)
    {
        if (!in_array($jenis, ['buku', 'anggota', 'peminjaman', 'denda'])) {
            abort(422, 'Jenis laporan tidak valid.');
        }

        $periodeAwal = $request->query('periode_awal');
        $periodeAkhir = $request->query('periode_akhir');

        // Log report generation for the PDF export as well
        Laporan::create([
            'id_admin' => $request->user()->id_admin,
            'jenis_laporan' => $jenis . '_pdf',
            'tgl_generate' => Carbon::now(),
            'periode_awal' => $periodeAwal ?: null,
            'periode_akhir' => $periodeAkhir ?: null,
        ]);

        $reportData = $this->getReportData($jenis, $periodeAwal, $periodeAkhir);

        $params = [
            'title' => 'LAPORAN REKAPITULASI ' . strtoupper($jenis),
            'jenis' => $jenis,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'data' => $reportData,
            'tgl_cetak' => Carbon::now()->isoFormat('D MMMM YHH:mm')
        ];

        // Set paper orientation (landscape for table data, portrait is fine but landscape is better for tables)
        $paperOrientation = 'portrait';
        if ($jenis === 'peminjaman' || $jenis === 'denda') {
            $paperOrientation = 'landscape';
        }

        $pdf = Pdf::loadView('pdf.laporan_' . $jenis, $params)
                  ->setPaper('a4', $paperOrientation);

        return $pdf->download('laporan_' . $jenis . '_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Get summary statistics for admin dashboard.
     */
    public function dashboardSummary(Request $request)
    {
        $totalBuku = Buku::count();
        $totalStok = (int) Buku::sum('stok');
        $totalAnggota = Anggota::count();
        $peminjamanAktif = Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count();
        $dendaBelumLunas = Denda::where('status_bayar', 'belum_bayar')->sum('total_denda');

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan dashboard berhasil diambil.',
            'data' => [
                'total_buku' => $totalBuku,
                'total_stok' => $totalStok,
                'total_anggota' => $totalAnggota,
                'peminjaman_aktif' => $peminjamanAktif,
                'denda_belum_lunas' => (double) $dendaBelumLunas,
            ]
        ]);
    }
}
