<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle the multi-guard login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_key' => 'required|string',
            'password' => 'required|string',
        ], [
            'login_key.required' => 'Username, Email, atau NIM wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $loginKey = $request->input('login_key');
        $password = $request->input('password');

        // 1. Check Admin table
        $admin = Admin::where('username', $loginKey)
            ->orWhere('email', $loginKey)
            ->first();

        if ($admin && Hash::check($password, $admin->password)) {
            $token = $admin->createToken('admin-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Admin',
                'data' => [
                    'token' => $token,
                    'role' => 'admin',
                    'user' => [
                        'id_admin' => $admin->id_admin,
                        'nama' => $admin->nama,
                        'username' => $admin->username,
                        'email' => $admin->email
                    ]
                ]
            ]);
        }

        // 2. Check Anggota table
        $anggota = Anggota::where('email', $loginKey)
            ->orWhere('nim', $loginKey)
            ->first();

        if ($anggota && Hash::check($password, $anggota->password)) {
            if ($anggota->status !== 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anggota Anda dinonaktifkan. Silakan hubungi admin.',
                    'data' => null
                ], 403);
            }

            $token = $anggota->createToken('anggota-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai Anggota',
                'data' => [
                    'token' => $token,
                    'role' => 'anggota',
                    'user' => [
                        'id_anggota' => $anggota->id_anggota,
                        'nama' => $anggota->nama,
                        'nim' => $anggota->nim,
                        'email' => $anggota->email,
                        'no_telepon' => $anggota->no_telepon,
                        'status' => $anggota->status
                    ]
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kredensial login salah.',
            'data' => null
        ], 401);
    }

    /**
     * Handle member registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'nim' => 'required|string|min:8|max:16|unique:anggota,nim',
            'email' => 'required|email|max:100|unique:anggota,email',
            'no_telepon' => 'nullable|string|max:15',
            'password' => 'required|string|min:6',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.min' => 'NIM minimal terdiri dari 8 digit.',
            'nim.max' => 'NIM maksimal terdiri dari 16 digit.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi pendaftaran gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $anggota = Anggota::create([
            'nama' => $request->input('nama'),
            'nim' => $request->input('nim'),
            'email' => $request->input('email'),
            'no_telepon' => $request->input('no_telepon'),
            'password' => Hash::make($request->input('password')),
            'tanggal_daftar' => now()->toDateString(),
            'status' => 'aktif',
            'id_admin' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran anggota berhasil! Silakan masuk.',
            'data' => [
                'id_anggota' => $anggota->id_anggota,
                'nama' => $anggota->nama,
                'nim' => $anggota->nim,
                'email' => $anggota->email,
            ]
        ], 201);
    }

    /**
     * Get authenticated user details.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $role = $user instanceof Admin ? 'admin' : 'anggota';

        $userData = [];
        if ($user instanceof Admin) {
            $userData = [
                'id_admin' => $user->id_admin,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
            ];
        } else {
            $userData = [
                'id_anggota' => $user->id_anggota,
                'nama' => $user->nama,
                'nim' => $user->nim,
                'email' => $user->email,
                'no_telepon' => $user->no_telepon,
                'status' => $user->status,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Data user aktif berhasil diambil.',
            'data' => [
                'role' => $role,
                'user' => $userData
            ]
        ]);
    }

    /**
     * Handle logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token dihapus.',
            'data' => null
        ]);
    }
}
