<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use Illuminate\Http\Request;

class WasteEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteEntry::with(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured', 'sourceLocation', 'attachment'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            // Dikelompokkan agar OR tidak membatalkan filter tanggal
            $query->where(function ($w) use ($search) {
                $w->whereHas('subCategory', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user.picDetail', fn($q) => $q->where('full_name', 'like', "%{$search}%"))
                  ->orWhereHas('sourceLocation', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('subCategory', fn($q) => $q->where('id_waste_category', $request->category));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $entries = $query->paginate(15)->withQueryString();
        $categories = \App\Models\WasteCategory::orderBy('name')->get();
        return view('pages.waste-entry.index', compact('entries', 'categories'));
    }

    public function show(WasteEntry $wasteEntry)
    {
        $wasteEntry->load(['user.picDetail', 'subCategory.category', 'subCategory.unitMeasured', 'subCategory.b3Detail', 'sourceLocation', 'attachment']);
        return view('pages.waste-entry.show', compact('wasteEntry'));
    }
}
