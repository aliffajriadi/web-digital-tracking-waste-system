<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteCategory;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request, StockService $stock)
    {
        $rows = $stock->stockList(onlyActive: false);

        $summary = [
            'items' => $rows->count(),
            'available' => $rows->where('stock', '>', 0)->count(),
            'empty' => $rows->filter(fn ($r) => abs($r['stock']) < 0.0001)->count(),
            'negative' => $rows->where('stock', '<', 0)->count(),
            'total_raw' => $rows->where('type', 'raw')->where('unit', 'kg')->sum('stock'),
            'total_processed' => $rows->where('type', 'processed')->where('unit', 'kg')->sum('stock'),
        ];

        if ($request->filled('search')) {
            $search = mb_strtolower($request->search);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name']), $search));
        }
        if ($request->filled('category')) {
            $rows = $request->category === 'processed'
                ? $rows->where('type', 'processed')
                : $rows->where('id_category', (int) $request->category);
        }
        if ($request->filled('status')) {
            $rows = match ($request->status) {
                'available' => $rows->where('stock', '>', 0),
                'empty' => $rows->filter(fn ($r) => abs($r['stock']) < 0.0001),
                'negative' => $rows->where('stock', '<', 0),
                'b3' => $rows->where('is_b3', true),
                default => $rows,
            };
        }

        $categories = WasteCategory::orderBy('name')->get();
        $b3Alerts = $stock->b3Alerts(null);

        return view('pages.stock.index', [
            'rows' => $rows->values(),
            'summary' => $summary,
            'categories' => $categories,
            'b3Alerts' => $b3Alerts,
        ]);
    }
}
