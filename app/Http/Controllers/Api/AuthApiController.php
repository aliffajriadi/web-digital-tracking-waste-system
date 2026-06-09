<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PicDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi inputan dari Flutter
        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        // 2. Cari NIK di tabel pic_details
        $picDetail = PicDetail::where('nik', $request->nik)->first();

        // Jika NIK tidak ditemukan
        if (!$picDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Induk Karyawan (NIK) tidak terdaftar.',
            ], 404);
        }

        // 3. Ambil data User dari relasi id_user yang ada di pic_details
        $user = User::where('id', $picDetail->id_user)->first();

        // 4. Cocokkan password-nya (Menggunakan Hash::check karena password di-bcrypt)
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi yang Anda masukkan salah.',
            ], 401);
        }

        // 5. Cek apakah akun statusnya aktif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh Admin.',
            ], 403);
        }

        // 6. BUAT TOKEN KEAMANAN (Sanctum)
        // Token ini yang akan disimpan oleh Flutter untuk akses API selanjutnya
        $token = $user->createToken('mobile_token')->plainTextToken;

        // 7. Berikan response SUKSES dalam bentuk JSON ke Flutter
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $picDetail->full_name,
                'nik' => $picDetail->nik,
            ]
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        // 1. Validasi input dari Flutter
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // 2. AMBIL USER LANGSUNG DARI SANCTUM 
        $user = $request->user();

        // 3. Ambil data PicDetail yang nempel dengan user ini
        $picDetail = PicDetail::where('id_user', $user->id)->first();

        if (!$picDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail profil karyawan tidak ditemukan.'
            ], 404);
        }

        // 4. Validasi tambahan: Pastikan email baru tidak duplikat dengan milik orang lain
        $emailCheck = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
        if ($emailCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah digunakan oleh akun lain.'
            ], 422);
        }

        // 5. UPDATE DATABASE NYA 
        // A. Update nama lengkap di tabel pic_details
        $picDetail->full_name = $request->name;
        $picDetail->save();

        // B. Update email di tabel users
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user'    => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $picDetail->full_name,
                'nik' => $picDetail->nik,
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        // 1. Validasi input dari Flutter 
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|string|min:6', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // 2. Ambil user yang sedang login secara langsung
        $user = $request->user();

        // 3. VALIDASI: Cocokkan kata sandi lama dengan yang ada di database
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama yang Anda masukkan salah.'
            ], 401);
        }

        // 4. UPDATE PASSWORD BARU (Wajib di-hash pakai bcrypt)
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui!'
        ], 200);
    }
}