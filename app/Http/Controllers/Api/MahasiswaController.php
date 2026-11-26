<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Mahasiswa::latest()->get();

        return response()->json([
            'status'  => true,
            'message' => 'Daftar data mahasiswa berhasil diambil.',
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nim'     => ['required', 'string', 'max:20', 'unique:mahasiswas,nim'],
            'nama'    => ['required', 'string', 'max:120'],
            'jurusan' => ['required', 'string', 'max:100'],
        ], [
            'nim.required'     => 'NIM wajib diisi.',
            'nim.unique'       => 'NIM sudah terdaftar.',
            'nama.required'    => 'Nama wajib diisi.',
            'jurusan.required' => 'Jurusan wajib diisi.',
        ]);

        $mahasiswa = Mahasiswa::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Data mahasiswa berhasil ditambahkan.',
            'data'    => $mahasiswa,
        ], 201);
    }

    public function show(Mahasiswa $mahasiswa): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Detail data mahasiswa.',
            'data'    => $mahasiswa,
        ], 200);
    }

    public function update(Request $request, Mahasiswa $mahasiswa): JsonResponse
    {
        $validated = $request->validate([
            'nim'     => ['required', 'string', 'max:20', Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa->id)],
            'nama'    => ['required', 'string', 'max:120'],
            'jurusan' => ['required', 'string', 'max:100'],
        ], [
            'nim.required'     => 'NIM wajib diisi.',
            'nim.unique'       => 'NIM sudah digunakan mahasiswa lain.',
            'nama.required'    => 'Nama wajib diisi.',
            'jurusan.required' => 'Jurusan wajib diisi.',
        ]);

        $mahasiswa->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Data mahasiswa berhasil diperbarui.',
            'data'    => $mahasiswa,
        ], 200);
    }

    public function destroy(Mahasiswa $mahasiswa): JsonResponse
    {
        $mahasiswa->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Data mahasiswa berhasil dihapus.',
        ], 200);
    }
}