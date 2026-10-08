<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteSubCategory;
use App\Models\WasteCategory;
use App\Models\WasteB3Detail;
use App\Models\UnitMeasured;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WasteSubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteSubCategory::with(['category', 'b3Detail', 'unitMeasured'])->orderBy('id_waste_category')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('category')) {
            $query->where('id_waste_category', $request->category);
        }

        $subCategories = $query->paginate(10)->withQueryString();
        $categories    = WasteCategory::orderBy('name')->get();
        $b3Details     = WasteB3Detail::orderBy('waste_code')->get();
        $units         = UnitMeasured::orderBy('name')->get();

        return view('pages.waste-subcategory.index', compact('subCategories', 'categories', 'b3Details', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_waste_category'   => ['required', 'exists:waste_category,id'],
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'id_waste_b3_detail'  => ['nullable', 'exists:waste_b3_detail,id'],
            'id_unit_measured'    => ['required', 'exists:unit_measured,id'],
            'default_measured_qty'=> ['required', 'numeric', 'min:0'],
            'is_active'           => ['boolean'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('subcategories', 'public');
        }

        WasteSubCategory::create($validated);
        return back()->with('success', 'Sub-kategori berhasil ditambahkan.');
    }

    public function update(Request $request, WasteSubCategory $wasteSubcategory)
    {
        $validated = $request->validate([
            'id_waste_category'   => ['required', 'exists:waste_category,id'],
            'name'                => ['required', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'id_waste_b3_detail'  => ['nullable', 'exists:waste_b3_detail,id'],
            'id_unit_measured'    => ['required', 'exists:unit_measured,id'],
            'default_measured_qty'=> ['required', 'numeric', 'min:0'],
            'is_active'           => ['boolean'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            if ($wasteSubcategory->photo) {
                Storage::disk('public')->delete($wasteSubcategory->photo);
            }
            $validated['photo'] = $request->file('photo')->store('subcategories', 'public');
        }

        $wasteSubcategory->update($validated);
        return back()->with('success', 'Sub-kategori berhasil diperbarui.');
    }

    public function destroy(WasteSubCategory $wasteSubcategory)
    {
        $used = $wasteSubcategory->wasteEntries()->exists()
            || \Illuminate\Support\Facades\DB::table('data_waste_out')->where('id_waste_sub_category', $wasteSubcategory->id)->exists()
            || \Illuminate\Support\Facades\DB::table('waste_raw_materials')->where('id_waste_sub_category', $wasteSubcategory->id)->exists();

        if ($used) {
            return back()->with('error', 'Sub-kategori sudah dipakai pada transaksi sehingga tidak dapat dihapus. Ubah statusnya menjadi Nonaktif agar tidak muncul lagi di aplikasi.');
        }

        if ($wasteSubcategory->photo) {
            Storage::disk('public')->delete($wasteSubcategory->photo);
        }
        $wasteSubcategory->delete();
        return back()->with('success', 'Sub-kategori berhasil dihapus.');
    }
}
