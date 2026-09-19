<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class EnsureBusinessAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $businessRoute = $request->route('business');
        if ($businessRoute !== null && $businessRoute !== '' && (int) $businessRoute !== (int) $user->business_id) {
            return response()->json(['message' => 'You do not have access to this business.'], 403);
        }

        $productRoute = $request->route('product');
        if ($productRoute !== null && $productRoute !== '') {
            $productId = $productRoute instanceof Product ? $productRoute->id : (int) $productRoute;
            $product = Product::find($productId);

            if ($product && (int) $product->business_id !== (int) $user->business_id) {
                return response()->json(['message' => 'You do not have access to this business.'], 403);
            }
        }

        return $next($request);
    }
}
