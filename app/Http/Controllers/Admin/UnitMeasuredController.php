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
        $used = \Illuminate\Support\Facades\DB::table('waste_sub_category')->where('id_unit_measured', $unitMeasured->id)->exists()
            || \Illuminate\Support\Facades\DB::table('processed_waste')->where('id_unit_measured', $unitMeasured->id)->exists();
        if ($used) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena masih dipakai oleh sub-kategori atau jenis olahan.');
        }
        $unitMeasured->delete();
        return back()->with('success', 'Satuan ukur berhasil dihapus.');
    }
}
