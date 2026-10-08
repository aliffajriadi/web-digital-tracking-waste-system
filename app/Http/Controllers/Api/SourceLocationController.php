<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SourceLocationController extends Controller
{
    public function index()
    {
        $locations = DB::table('source_location_waste')->orderBy('name')->get()->map(function ($loc) {
            $loc->photo_url = $loc->photo ? asset('storage/' . $loc->photo) : null;
            return $loc;
        });

        return response()->json([
            'success' => true,
            'data' => $locations,
        ], 200);
    }
}
