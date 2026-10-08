<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteSubCategory;
use App\Services\StockService;

class SubCategoryController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function getByCategoryId($categoryId)
    {
        $subCategories = WasteSubCategory::with(['unitMeasured', 'b3Detail'])
            ->where('id_waste_category', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stocks = $this->stock->rawStocks($subCategories->pluck('id')->all());
        $subCategories->each(function ($sub) use ($stocks) {
            $sub->setAttribute('stock', (float) ($stocks[$sub->id] ?? 0));
            $sub->setAttribute('unit', $sub->unitMeasured?->symbol ?? 'kg');
        });

        return response()->json([
            'success' => true,
            'data' => $subCategories,
        ], 200);
    }
}
