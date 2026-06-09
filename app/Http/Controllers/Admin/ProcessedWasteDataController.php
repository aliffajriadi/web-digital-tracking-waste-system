<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessedWasteData;
use Illuminate\Http\Request;

class ProcessedWasteDataController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessedWasteData::with(['processedWaste', 'user.picDetail'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $processedData = $query->paginate(15)->withQueryString();
        return view('pages.processed-waste-data.index', compact('processedData'));
    }

    public function show(ProcessedWasteData $processedWasteData)
    {
        $processedWasteData->load(['processedWaste', 'user.picDetail']);
        return view('pages.processed-waste-data.show', compact('processedWasteData'));
    }
}
