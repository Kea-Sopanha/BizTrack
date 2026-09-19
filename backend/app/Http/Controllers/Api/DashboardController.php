<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\WasteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'owner') {
            return response()->json(['message' => 'Owner access only.'], 403);
        }

        $business = $request->user()->business;
        $today = now()->toDateString();

        $sales = Sale::where('business_id', $business->id)
            ->whereDate('sold_at', $today)
            ->get();

        $purchases = Purchase::where('business_id', $business->id)
            ->whereDate('purchase_date', $today)
            ->sum('total_amount');

        $expenses = Expense::where('business_id', $business->id)
            ->whereDate('expense_date', $today)
            ->sum('amount');

        $waste = WasteRecord::where('business_id', $business->id)
            ->whereDate('recorded_at', $today)
            ->sum(DB::raw('quantity * unit_cost_snapshot'));

        return response()->json([
            'business' => $business,
            'today_revenue' => round($sales->sum('total_amount'), 2),
            'today_expenses' => round($expenses, 2),
            'purchase_value' => round($purchases, 2),
            'waste_cost' => round($waste, 2),
            'number_of_sales' => $sales->count(),
        ]);
    }
}
