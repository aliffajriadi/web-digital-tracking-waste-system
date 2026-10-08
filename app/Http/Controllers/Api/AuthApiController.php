<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActivePic;
use App\Models\IotAuthSession;
use App\Models\PicDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi inputan dari Flutter
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Cari NIK di tabel pic_detail, lalu cocokkan password.
        // Pesan dibuat sama untuk NIK/password salah agar NIK tidak bisa ditebak.
        $picDetail = PicDetail::where('nik', trim($request->nik))->first();
        $user = $picDetail ? User::find($picDetail->id_user) : null;

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'NIK atau kata sandi salah.',
            ], 401);
        }

        if ((int) $user->role_id !== EnsureActivePic::ROLE_PIC) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki akses ke aplikasi PIC.',
            ], 403);
        }

        // 3. Cek apakah akun statusnya aktif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh Admin.',
            ], 403);
        }

        // 4. Buat token Sanctum yang akan disimpan aplikasi untuk request selanjutnya
        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $this->userPayload($user, $picDetail),
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user, $user->picDetail),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
            'photo' => 'nullable|image|max:2048',
        ], [
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $picDetail = PicDetail::where('id_user', $user->id)->first();

        if (!$picDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail profil karyawan tidak ditemukan.'
            ], 404);
        }

        $picDetail->full_name = $request->name;
        if ($request->has('phone')) {
            $picDetail->phone = $request->phone;
        }
        $picDetail->save();

        $user->email = $request->email;

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('user_photos', 'public');
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'user'    => $this->userPayload($user, $picDetail),
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|string|min:8|different:old_password',
        ], [
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.different' => 'Kata sandi baru harus berbeda dari kata sandi lama.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama yang Anda masukkan salah.',
                'errors' => ['old_password' => ['Kata sandi lama yang Anda masukkan salah.']],
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Keluarkan sesi di perangkat lain, pertahankan sesi yang sedang dipakai.
        $current = $user->currentAccessToken();
        $user->tokens()
            ->when($current instanceof PersonalAccessToken, fn ($q) => $q->where('id', '!=', $current->id))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui!'
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // Putuskan timbangan IoT agar tidak lagi mencatat atas nama PIC yang keluar.
        IotAuthSession::where('id_user', $user->id)
            ->whereIn('status', ['pending', 'paired'])
            ->delete();

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }

    private function userPayload(User $user, ?PicDetail $picDetail): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'full_name' => $picDetail?->full_name ?? $user->email,
            'nik' => $picDetail?->nik ?? '',
            'phone' => $picDetail?->phone,
            'photo' => $user->photo,
            'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
        ];
    }
}
