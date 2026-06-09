<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteSubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        try {
            // Mengambil sub-kategori yang id_waste_category-nya cocok
            $subCategories = WasteSubCategory::where('id_waste_category', $categoryId)
                ->where('is_active', 1) // Hanya ambil yang aktif
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subCategories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data sub-kategori: ' . $e->getMessage()
            ], 500);
        }
    }
}