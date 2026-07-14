<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Http\Requests\StoreBukuRequest;
use App\Http\Requests\UpdateBukuRequest;
use App\Http\Resources\BukuResource;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Buku::with('admin');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        $perPage = $request->input('per_page', 10);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar buku berhasil diambil.',
            'data' => BukuResource::collection($books)->response()->getData(true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBukuRequest $request)
    {
        $validated = $request->validated();
        
        // Admin ID from authenticated user
        $validated['id_admin'] = $request->user()->id_admin;
        if (!isset($validated['stok'])) {
            $validated['stok'] = 0;
        }

        $buku = Buku::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan.',
            'data' => new BukuResource($buku)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $buku = Buku::with('admin')->find($id);

        if (!$buku) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail buku berhasil diambil.',
            'data' => new BukuResource($buku)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBukuRequest $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $validated = $request->validated();
        $buku->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil diperbarui.',
            'data' => new BukuResource($buku)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan.',
                'data' => null
            ], 404);
        }

        // Check if there are active loans
        $activeLoansCount = Peminjaman::where('id_buku', $id)
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->count();

        if ($activeLoansCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak dapat dihapus karena masih terdapat transaksi peminjaman aktif.',
                'data' => null
            ], 422);
        }

        $buku->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus.',
            'data' => null
        ]);
    }
}
