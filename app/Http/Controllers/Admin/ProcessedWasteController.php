<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWaste;
use App\Models\UnitMeasured;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessedWasteController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessedWaste::with('unitMeasured');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $processedWastes = $query->paginate(10)->withQueryString();
        $units = UnitMeasured::orderBy('name')->get();
        return view('pages.processed-waste.index', compact('processedWastes', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'id_unit_measured'    => ['required', 'exists:unit_measured,id'],
            'default_measured_qty'=> ['required', 'numeric', 'min:0'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('processed', 'public');
        }

        ProcessedWaste::create($validated);
        return back()->with('success', 'Jenis olahan berhasil ditambahkan.');
    }

    public function update(Request $request, ProcessedWaste $processedWaste)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'id_unit_measured'    => ['required', 'exists:unit_measured,id'],
            'default_measured_qty'=> ['required', 'numeric', 'min:0'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($processedWaste->photo) {
                Storage::disk('public')->delete($processedWaste->photo);
            }
            $validated['photo'] = $request->file('photo')->store('processed', 'public');
        }

        $processedWaste->update($validated);
        return back()->with('success', 'Jenis olahan berhasil diperbarui.');
    }

    public function destroy(ProcessedWaste $processedWaste)
    {
        $processedWaste->delete();
        return back()->with('success', 'Jenis olahan berhasil dihapus.');
    }
}
