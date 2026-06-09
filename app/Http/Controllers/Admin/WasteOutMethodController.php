<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteOutMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WasteOutMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteOutMethod::withCount('wasteOutData');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $methods = $query->paginate(10)->withQueryString();
        return view('pages.waste-out-method.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:waste_out_method,name'],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('methods', 'public');
        }

        WasteOutMethod::create($validated);
        return back()->with('success', 'Metode keluar sampah berhasil ditambahkan.');
    }

    public function update(Request $request, WasteOutMethod $wasteOutMethod)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', "unique:waste_out_method,name,{$wasteOutMethod->id}"],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($wasteOutMethod->photo) {
                Storage::disk('public')->delete($wasteOutMethod->photo);
            }
            $validated['photo'] = $request->file('photo')->store('methods', 'public');
        }

        $wasteOutMethod->update($validated);
        return back()->with('success', 'Metode keluar sampah berhasil diperbarui.');
    }

    public function destroy(WasteOutMethod $wasteOutMethod)
    {
        if ($wasteOutMethod->wasteOutData()->count() > 0) {
            return back()->with('error', 'Metode tidak dapat dihapus karena sudah digunakan pada data sampah keluar.');
        }

        $wasteOutMethod->delete();
        return back()->with('success', 'Metode keluar sampah berhasil dihapus.');
    }
}
