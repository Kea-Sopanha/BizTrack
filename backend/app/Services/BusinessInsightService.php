<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\WasteRecord;
use Illuminate\Support\Facades\DB;

class BusinessInsightService
{
    public function generate(Business $business): array
    {
        $today = now()->toDateString();
        $weekAgo = now()->subDays(7)->toDateString();

        $currentRevenue = Sale::where('business_id', $business->id)
            ->whereBetween('sold_at', [$weekAgo, $today . ' 23:59:59'])
            ->sum('total_amount');

        $previousRevenue = Sale::where('business_id', $business->id)
            ->whereBetween('sold_at', [now()->subDays(14)->toDateString(), now()->subDays(7)->toDateString() . ' 23:59:59'])
            ->sum('total_amount');

        $change = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        $topProduct = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.business_id', $business->id)
            ->select('sale_items.product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_qty')
            ->first();

        $wasteDelta = WasteRecord::where('business_id', $business->id)
            ->whereBetween('recorded_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('quantity');

        $lowStockProducts = DB::table('products')
            ->where('business_id', $business->id)
            ->whereColumn('stock_quantity', '<', 'minimum_stock')
            ->count();

        return [
            [
                'type' => 'revenue_growth',
                'label' => 'Revenue trend',
                'text' => 'Revenue is ' . ($change >= 0 ? 'up ' : 'down ') . abs(round($change, 1)) . '% compared with the previous week.',
                'percentage_change' => round($change, 2),
            ],
            [
                'type' => 'top_product',
                'label' => 'Top-selling product',
                'text' => $topProduct ? 'The strongest-selling product this week is product #' . $topProduct->product_id . ' with ' . $topProduct->total_qty . ' units sold.' : 'No sales recorded yet.',
                'value' => $topProduct ? (int) $topProduct->total_qty : 0,
            ],
            [
                'type' => 'stock_alert',
                'label' => 'Low stock',
                'text' => $lowStockProducts . ' products are below minimum stock.',
                'value' => $lowStockProducts,
            ],
            [
                'type' => 'waste_review',
                'label' => 'Waste review',
                'text' => 'Waste volume this week is ' . $wasteDelta . ' units, so review prep levels and damaged stock handling.',
                'value' => $wasteDelta,
            ],
        ];
    }
}
