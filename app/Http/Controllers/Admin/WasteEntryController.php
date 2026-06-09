<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteEntry;
use Illuminate\Http\Request;

class WasteEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteEntry::with(['user.picDetail', 'subCategory.category', 'sourceLocation'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subCategory', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('user.picDetail', fn($q) => $q->where('full_name', 'like', "%{$search}%"));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $entries = $query->paginate(15)->withQueryString();
        return view('pages.waste-entry.index', compact('entries'));
    }

    public function show(WasteEntry $wasteEntry)
    {
        $wasteEntry->load(['user.picDetail', 'subCategory.category', 'sourceLocation', 'attachment']);
        return view('pages.waste-entry.show', compact('wasteEntry'));
    }
}
