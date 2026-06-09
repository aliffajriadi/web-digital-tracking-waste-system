<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitMeasured;
use Illuminate\Http\Request;

class UnitMeasuredController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitMeasured::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $units = $query->paginate(15)->withQueryString();
        return view('pages.unit-measured.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:50'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'type'   => ['required', 'in:weight,volume,count,length'],
        ]);
        UnitMeasured::create($validated);
        return back()->with('success', 'Satuan ukur berhasil ditambahkan.');
    }

    public function update(Request $request, UnitMeasured $unitMeasured)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:50'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'type'   => ['required', 'in:weight,volume,count,length'],
        ]);
        $unitMeasured->update($validated);
        return back()->with('success', 'Satuan ukur berhasil diperbarui.');
    }

    public function destroy(UnitMeasured $unitMeasured)
    {
        $unitMeasured->delete();
        return back()->with('success', 'Satuan ukur berhasil dihapus.');
    }
}
