<?php

namespace App\Services;

class AiSummaryService
{
    public function summarize(array $metrics): array
    {
        $revenue = (float) ($metrics['revenue'] ?? 0);
        $previousRevenue = (float) ($metrics['previous_revenue'] ?? 0);
        $profit = (float) ($metrics['profit'] ?? 0);
        $topProduct = $metrics['top_product'] ?? 'No top item';
        $waste = (float) ($metrics['waste'] ?? 0);

        $change = $previousRevenue > 0 ? (($revenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'summary' => [
                'headline' => 'Revenue is ' . ($change >= 0 ? 'up' : 'down') . ' ' . abs(round($change, 1)) . '% compared with the previous period.',
                'details' => [
                    'Top product: ' . $topProduct,
                    'Current profit: $' . number_format($profit, 2),
                    'Waste impact: $' . number_format($waste, 2),
                ],
            ],
            'provider' => 'rule-based-ai-summary',
        ];
    }
}
