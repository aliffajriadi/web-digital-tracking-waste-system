<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryReport;
use Illuminate\Http\Request;

class CategoryReportController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoryReport::withCount('reports')->latest('id');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->paginate(15)->withQueryString();
        return view('pages.category-report.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:category_report,name',
        ]);

        CategoryReport::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.category-report.index')->with('success', 'Kategori laporan berhasil ditambahkan.');
    }

    public function update(Request $request, CategoryReport $categoryReport)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:category_report,name,' . $categoryReport->id,
        ]);

        $categoryReport->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.category-report.index')->with('success', 'Kategori laporan berhasil diperbarui.');
    }

    public function destroy(CategoryReport $categoryReport)
    {
        if ($categoryReport->reports()->count() > 0) {
            return redirect()->route('admin.category-report.index')->with('error', 'Kategori ini tidak dapat dihapus karena sudah digunakan dalam laporan.');
        }

        $categoryReport->delete();
        return redirect()->route('admin.category-report.index')->with('success', 'Kategori laporan berhasil dihapus.');
    }
}
