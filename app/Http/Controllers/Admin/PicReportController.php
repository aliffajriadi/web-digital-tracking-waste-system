<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class PicReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['user.picDetail', 'categoryReport', 'attachment'])->latest('id');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $reports = $query->paginate(15)->withQueryString();
        return view('pages.pic-report.index', compact('reports'));
    }

    public function show(Report $pic_report)
    {
        $pic_report->load(['user.picDetail', 'categoryReport', 'attachment']);
        return view('pages.pic-report.show', ['report' => $pic_report]);
    }
}
