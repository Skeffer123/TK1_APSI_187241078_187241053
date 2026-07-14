<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Http\Resources\AnggotaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Anggota::with('admin');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $anggota = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar anggota berhasil diambil.',
            'data' => AnggotaResource::collection($anggota)->response()->getData(true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnggotaRequest $request)
    {
        $validated = $request->validated();
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['id_admin'] = $request->user()->id_admin;
        $validated['status'] = 'aktif';
        $validated['tanggal_daftar'] = date('Y-m-d');

        $anggota = Anggota::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil didaftarkan.',
            'data' => new AnggotaResource($anggota)
        ], 201);
    }

    /**
     * Display the specified resource with loan history.
     */
    public function show($id)
    {
        // Eager load admin, and loans with their books and fines
        $anggota = Anggota::with(['admin', 'peminjaman.buku', 'peminjaman.denda'])->find($id);

        if (!$anggota) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail anggota berhasil diambil.',
            'data' => new AnggotaResource($anggota)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnggotaRequest $request, $id)
    {
        $anggota = Anggota::find($id);

        if (!$anggota) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $validated = $request->validated();

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $anggota->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data anggota berhasil diperbarui.',
            'data' => new AnggotaResource($anggota)
        ]);
    }

    /**
     * Deactivate a member (status -> nonaktif).
     */
    public function nonaktifkan(Request $request, $id)
    {
        $anggota = Anggota::find($id);

        if (!$anggota) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $anggota->status = 'nonaktif';
        $anggota->save();

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil dinonaktifkan.',
            'data' => new AnggotaResource($anggota)
        ]);
    }
}
