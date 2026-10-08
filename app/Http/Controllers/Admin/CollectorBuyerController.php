<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataCollectorBuyer;
use Illuminate\Http\Request;

class CollectorBuyerController extends Controller
{
    public function index(Request $request)
    {
        $query = DataCollectorBuyer::latest();
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }
        $collectors = $query->paginate(10)->withQueryString();
        return view('pages.collector-buyer.index', compact('collectors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address'      => ['required', 'string'],
            'email'        => ['required', 'email'],
            'website'      => ['nullable', 'url'],
            'notes'        => ['nullable', 'string'],
        ]);
        DataCollectorBuyer::create($validated);
        return back()->with('success', 'Data pengepul/pembeli berhasil ditambahkan.');
    }

    public function update(Request $request, DataCollectorBuyer $collectorBuyer)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address'      => ['required', 'string'],
            'email'        => ['required', 'email'],
            'website'      => ['nullable', 'url'],
            'notes'        => ['nullable', 'string'],
        ]);
        $collectorBuyer->update($validated);
        return back()->with('success', 'Data pengepul/pembeli berhasil diperbarui.');
    }

    public function destroy(DataCollectorBuyer $collectorBuyer)
    {
        if (\Illuminate\Support\Facades\DB::table('waste_selling_data')->where('id_buyer', $collectorBuyer->id)->exists()) {
            return back()->with('error', 'Pengepul/pembeli tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.');
        }
        $collectorBuyer->delete();
        return back()->with('success', 'Data pengepul/pembeli berhasil dihapus.');
    }
}
