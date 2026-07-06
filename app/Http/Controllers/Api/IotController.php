<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IotAuthSession;
use App\Models\WasteEntry;
use Illuminate\Support\Str;

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
        while (IotAuthSession::where('code', $code)->where('status', 'pending')->exists()) {
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

    // PIC pairs the code via mobile/web app
    public function pairCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'id_user' => 'required|exists:users,id'
        ]);

        $session = IotAuthSession::where('code', strtoupper($request->code))
            ->where('status', 'pending')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Code not found or already paired'], 404);
        }

        $session->update([
            'id_user' => $request->id_user,
            'status' => 'paired'
        ]);

        return response()->json(['success' => true, 'message' => 'Successfully paired with IoT device']);
    }

    // PIC logs out / unpairs the device
    public function unpairCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $session = IotAuthSession::where('code', strtoupper($request->code))->first();

        if ($session && in_array($session->status, ['pending', 'paired'])) {
            $session->delete();
            return response()->json(['success' => true, 'message' => 'Berhasil logout dari perangkat. Perangkat akan membuat kode baru.']);
        }

        return response()->json(['success' => false, 'message' => 'Kode tidak ditemukan atau sudah kadaluarsa'], 404);
    }

    // IoT device checks if code is paired
    public function checkStatus($code)
    {
        $session = IotAuthSession::with('user')->where('code', strtoupper($code))->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        if ($session->status === 'paired') {
            return response()->json([
                'success' => true,
                'status' => 'paired',
                'user' => $session->user,
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
            'id_waste_sub_category' => 'required|exists:waste_sub_category,id',
            'measured_qty' => 'required|numeric',
        ]);

        $session = IotAuthSession::where('code', strtoupper($request->code))
            ->where('status', 'paired')
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Invalid or unpaired session code'], 403);
        }

        // Store waste entry
        $entry = WasteEntry::create([
            'id_user' => $session->id_user,
            'id_waste_sub_category' => $request->id_waste_sub_category,
            'measured_qty' => $request->measured_qty,
            'notes' => 'Timbangan Otomatis (IoT)'
        ]);

        // Sesi TIDAK ditandai completed di sini.
        // Sesi tetap 'paired' sampai PIC menekan logout dari aplikasi mobile.

        return response()->json([
            'success' => true,
            'message' => 'Weight data saved successfully',
            'data' => $entry
        ]);
    }
}
