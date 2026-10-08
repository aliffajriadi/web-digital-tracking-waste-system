<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = WasteCategory::withCount(['subCategories' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ], 200);
    }
}
