<?php

namespace App\Http\Controllers\Api;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController
{
    public function daily(Request $request, ReportService $reportService)
    {
        $date = $request->query('date', now()->toDateString());

        return response()->json($reportService->dailySummary($request->user()->business, $date));
    }

    public function monthly(Request $request, ReportService $reportService)
    {
        $month = $request->query('month', now()->format('Y-m'));

        return response()->json($reportService->monthlySummary($request->user()->business, $month));
    }
}
