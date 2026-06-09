<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['user.picDetail', 'categoryReport'])->latest('id');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $reports = $query->paginate(15)->withQueryString();
        return view('pages.report.index', compact('reports'));
    }

    public function show(Report $report)
    {
        $report->load(['user.picDetail', 'categoryReport']);
        return view('pages.report.show', compact('report'));
    }
}
