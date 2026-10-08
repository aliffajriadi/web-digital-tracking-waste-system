<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IotAuthSession;
use App\Models\WasteEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class IotController extends Controller
{
    // Generate a new code for the IoT device
    public function generateCode()
    {
        // Delete older active sessions because there is only 1 device
        IotAuthSession::whereIn('status', ['pending', 'paired'])->delete();

        // Generate random 4 character alphanumeric code
        $code = strtoupper(Str::random(4));

        // Ensure it's unique
        while (IotAuthSession::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(4));
        }

        $session = IotAuthSession::create([
            'code' => $code,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'code' => $session->code,
            'message' => 'Code generated successfully'
        ]);
    }

    // Sesi timbangan yang sedang terhubung dengan PIC yang login (untuk memulihkan status di aplikasi)
    public function currentSession(Request $request)
    {
        $session = IotAuthSession::where('id_user', $request->user()->id)
            ->where('status', 'paired')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'paired' => (bool) $session,
            'code' => $session?->code,
            'paired_at' => $session?->updated_at?->toDateTimeString(),
        ]);
    }

    // PIC pairs the code via mobile app. Perangkat selalu dipasangkan ke akun yang sedang login.
    public function pairCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10',
        ], [
            'code.required' => 'Kode perangkat wajib diisi.',
        ]);

        $session = IotAuthSession::where('code', strtoupper(trim($request->code)))
            ->where('status', 'pending')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tidak ditemukan atau sudah dipakai. Periksa kode di layar timbangan.',
            ], 404);
        }

        $session->update([
            'id_user' => $request->user()->id,
            'status' => 'paired'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil terhubung dengan timbangan.',
            'code' => $session->code,
        ]);
    }

    // PIC logs out / unpairs the device
    public function unpairCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $session = IotAuthSession::where('code', strtoupper($request->code))->first();

        if ($session && $session->id_user && $session->id_user !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Perangkat ini terhubung dengan akun lain.'], 403);
        }

        if ($session && in_array($session->status, ['pending', 'paired'])) {
            $session->delete();
            return response()->json(['success' => true, 'message' => 'Berhasil memutus perangkat. Perangkat akan membuat kode baru.']);
        }

        return response()->json(['success' => false, 'message' => 'Kode tidak ditemukan atau sudah kadaluarsa'], 404);
    }

    // IoT device checks if code is paired
    public function checkStatus($code)
    {
        $session = IotAuthSession::with('user.picDetail')->where('code', strtoupper($code))->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        if ($session->status === 'paired') {
            return response()->json([
                'success' => true,
                'status' => 'paired',
                // Hanya data yang dibutuhkan layar timbangan, bukan seluruh data akun
                'user' => [
                    'id' => $session->user?->id,
                    'name' => $session->user?->picDetail?->full_name ?? $session->user?->email,
                ],
                'message' => 'Device is paired'
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => $session->status,
            'message' => 'Waiting for pairing'
        ]);
    }

    // IoT device sends the final weight data
    public function storeWeight(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:iot_auth_sessions,code',
            'id_waste_sub_category' => [
                'required',
                Rule::exists('waste_sub_category', 'id')->where('is_active', true),
            ],
            'measured_qty' => 'required|numeric|gt:0|max:100000',
        ]);

        $session = IotAuthSession::with('user')
            ->where('code', strtoupper($request->code))
            ->where('status', 'paired')
            ->first();

        if (!$session || !$session->user || !$session->user->is_active) {
            return response()->json(['success' => false, 'message' => 'Invalid or unpaired session code'], 403);
        }

        $entry = WasteEntry::create([
            'id_user' => $session->id_user,
            'id_waste_sub_category' => $request->id_waste_sub_category,
            'measured_qty' => $request->measured_qty,
            'notes' => 'Timbangan Otomatis (IoT)'
        ]);

        // Sesi TIDAK ditandai completed di sini.
        // Sesi tetap 'paired' sampai PIC memutus perangkat / logout dari aplikasi mobile.

        return response()->json([
            'success' => true,
            'message' => 'Weight data saved successfully',
            'data' => $entry
        ]);
    }
}
