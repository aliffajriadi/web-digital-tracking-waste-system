<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SourceLocationWaste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SourceLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = SourceLocationWaste::withCount('wasteEntries');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $locations = $query->paginate(10)->withQueryString();
        return view('pages.source-location.index', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'photo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('locations', 'public');
        }

        SourceLocationWaste::create($validated);
        return back()->with('success', 'Sumber lokasi berhasil ditambahkan.');
    }

    public function update(Request $request, SourceLocationWaste $sourceLocation)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'photo'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($sourceLocation->photo) {
                Storage::disk('public')->delete($sourceLocation->photo);
            }
            $validated['photo'] = $request->file('photo')->store('locations', 'public');
        }

        $sourceLocation->update($validated);
        return back()->with('success', 'Sumber lokasi berhasil diperbarui.');
    }

    public function destroy(SourceLocationWaste $sourceLocation)
    {
        $sourceLocation->delete();
        return back()->with('success', 'Sumber lokasi berhasil dihapus.');
    }
}
