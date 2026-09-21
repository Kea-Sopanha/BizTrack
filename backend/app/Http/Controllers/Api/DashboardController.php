<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Models\Product;
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

        $purchases = (float) Purchase::where('business_id', $business->id)
            ->whereDate('purchase_date', $today)
            ->sum('total_amount');

        $expenses = (float) Expense::where('business_id', $business->id)
            ->whereDate('expense_date', $today)
            ->sum('amount');

        $waste = (float) WasteRecord::where('business_id', $business->id)
            ->whereDate('recorded_at', $today)
            ->sum(DB::raw('quantity * unit_cost_snapshot'));

        $totalRevenue = (float) $sales->sum('total_amount');
        $salesCount = $sales->count();

        // ទាញយកបញ្ជីផលិតផលសម្រាប់ Dropdown Quick Sale / Purchase លើ Web
        $products = Product::where('business_id', $business->id)->get();

        return response()->json([
            'business' => $business,
            
            // Format សម្រាប់ Web Dashboard ថ្មី
            'revenue' => round($totalRevenue, 2),
            'expenses' => round($expenses, 2),
            'purchaseValue' => round($purchases, 2),
            'waste' => round($waste, 2),
            'salesCount' => $salesCount,
            'products' => $products,

            // Format ចាស់ (ការពារកុំឱ្យបាក់មុខងារផ្សេងទៀត)
            'today_revenue' => round($totalRevenue, 2),
            'today_expenses' => round($expenses, 2),
            'purchase_value' => round($purchases, 2),
            'waste_cost' => round($waste, 2),
            'number_of_sales' => $salesCount,
        ]);
    }
}