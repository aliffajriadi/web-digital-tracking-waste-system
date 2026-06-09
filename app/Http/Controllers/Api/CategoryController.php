<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteCategory; 
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            // Mengambil semua data kategori dari tabel waste_category
            $categories = WasteCategory::all();

            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}