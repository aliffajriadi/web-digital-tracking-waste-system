<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WasteCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteCategory::withCount('subCategories')->latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        $categories = $query->paginate(10)->withQueryString();
        return view('pages.waste-category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('categories', 'public');
        }

        WasteCategory::create($validated);
        return back()->with('success', 'Kategori sampah berhasil ditambahkan.');
    }

    public function update(Request $request, WasteCategory $wasteCategory)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'photo'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($wasteCategory->photo) {
                Storage::disk('public')->delete($wasteCategory->photo);
            }
            $validated['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $wasteCategory->update($validated);
        return back()->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    public function destroy(WasteCategory $wasteCategory)
    {
        if ($wasteCategory->subCategories()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki sub-kategori.');
        }
        $wasteCategory->delete();
        return back()->with('success', 'Kategori sampah berhasil dihapus.');
    }
}
