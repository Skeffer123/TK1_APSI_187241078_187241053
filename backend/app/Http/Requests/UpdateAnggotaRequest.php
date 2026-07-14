<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnggotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get target ID from route parameters (e.g. /api/anggota/{id})
        $id = $this->route('id') ?? $this->route('anggota');

        return [
            'nama' => 'sometimes|required|string|max:100',
            'nim' => 'sometimes|required|string|min:8|max:16|unique:anggota,nim,' . $id . ',id_anggota',
            'email' => 'sometimes|required|email|max:100|unique:anggota,email,' . $id . ',id_anggota',
            'no_telepon' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'status' => 'sometimes|required|in:aktif,nonaktif',
        ];
    }

    /**
     * Get custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama anggota wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.min' => 'NIM minimal terdiri dari 8 karakter.',
            'nim.max' => 'NIM maksimal terdiri dari 16 karakter.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ];
    }
}
