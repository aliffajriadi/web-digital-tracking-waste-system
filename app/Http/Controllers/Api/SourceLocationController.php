<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SourceLocationController extends Controller
{
    public function index()
    {
        // Mengambil semua lokasi sumber sampah
        $locations = DB::table('source_location_waste')->get();

        return response()->json([
            'success' => true,
            'data' => $locations
        ], 200);
    }
}