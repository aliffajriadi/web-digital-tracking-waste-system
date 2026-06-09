<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteB3Detail;
use Illuminate\Http\Request;

class WasteB3Controller extends Controller
{
    public function index(Request $request)
    {
        $query = WasteB3Detail::query();
        if ($request->filled('search')) {
            $query->where('waste_code', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
        }
        $b3Details = $query->paginate(10)->withQueryString();
        return view('pages.waste-b3.index', compact('b3Details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waste_code'           => ['required', 'string', 'max:50', 'unique:waste_b3_detail'],
            'description'          => ['required', 'string'],
            'retention_period_day' => ['required', 'integer', 'min:1'],
            'danger_level'         => ['required', 'integer', 'min:1', 'max:5'],
        ]);
        WasteB3Detail::create($validated);
        return back()->with('success', 'Data limbah B3 berhasil ditambahkan.');
    }

    public function update(Request $request, WasteB3Detail $wasteB3)
    {
        $validated = $request->validate([
            'waste_code'           => ['required', 'string', 'max:50', 'unique:waste_b3_detail,waste_code,' . $wasteB3->id],
            'description'          => ['required', 'string'],
            'retention_period_day' => ['required', 'integer', 'min:1'],
            'danger_level'         => ['required', 'integer', 'min:1', 'max:5'],
        ]);
        $wasteB3->update($validated);
        return back()->with('success', 'Data limbah B3 berhasil diperbarui.');
    }

    public function destroy(WasteB3Detail $wasteB3)
    {
        $wasteB3->delete();
        return back()->with('success', 'Data limbah B3 berhasil dihapus.');
    }
}
