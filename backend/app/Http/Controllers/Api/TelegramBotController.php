<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\User;
use App\Models\WasteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TelegramBotController
{
    public function webhook(Request $request)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $secret = env('TELEGRAM_WEBHOOK_SECRET');

        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $update = $request->all();
        $message = $update['message'] ?? $update['edited_message'] ?? null;

        if (! $message) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim((string) ($message['text'] ?? ''));

        if (! $chatId || $text === '') {
            return response()->json(['ok' => true]);
        }

        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            if ($token) {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => 'BizTrack Telegram is not linked to your account yet. Please connect your Telegram chat in the BizTrack app first.',
                ]);
            }

            return response()->json(['ok' => true]);
        }

        $trimmed = strtolower($text);
        $reply = null;

        if (in_array($trimmed, ['help', '/help', 'menu'])) {
            $reply = "BizTrack commands:\n- sale <product> <qty> <price>\n- purchase <product> <qty> <cost>\n- waste <product> <qty> <reason>\n- start shift\n- close shift\n- today\nExample: sale Milk 4 3.5";
        }
        elseif (preg_match('/^sale\s+(.+?)\s+(\d+)\s+([0-9]+(?:\.[0-9]+)?)$/i', $text, $matches)) {
            $productName = trim($matches[1]);
            $quantity = (int) $matches[2];
            $unitPrice = (float) $matches[3];

            $product = Product::where('business_id', $user->business_id)
                ->where(function ($query) use ($productName) {
                    $query->whereRaw('LOWER(name) = ?', [strtolower($productName)])
                        ->orWhereRaw('LOWER(sku) = ?', [strtolower($productName)]);
                })
                ->first();

            if (! $product) {
                $reply = "Product '{$productName}' was not found in your business stock.";
            }
            elseif ($product->stock_quantity < $quantity) {
                $reply = "Not enough stock for {$product->name}. Available: {$product->stock_quantity}.";
            }
            else {
                DB::transaction(function () use ($user, $product, $quantity, $unitPrice) {
                    $sale = Sale::create([
                        'business_id' => $user->business_id,
                        'shift_id' => Shift::where('business_id', $user->business_id)->where('user_id', $user->id)->where('status', 'open')->value('id'),
                        'user_id' => $user->id,
                        'sale_no' => 'TG-SALE-' . now()->timestamp,
                        'sold_at' => now(),
                        'total_amount' => 0,
                        'notes' => 'Telegram bot sale',
                    ]);

                    $lineTotal = $quantity * $unitPrice;
                    $sale->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'unit_cost_snapshot' => $product->default_cost,
                        'line_total' => $lineTotal,
                    ]);

                    $product->stock_quantity = (int) $product->stock_quantity - $quantity;
                    $product->save();

                    $sale->total_amount = $lineTotal;
                    $sale->save();
                });

                $reply = "Sale recorded successfully for {$quantity} {$product->name}.";
            }
        }
        elseif (preg_match('/^purchase\s+(.+?)\s+(\d+)\s+([0-9]+(?:\.[0-9]+)?)$/i', $text, $matches)) {
            $productName = trim($matches[1]);
            $quantity = (int) $matches[2];
            $unitCost = (float) $matches[3];

            $product = Product::where('business_id', $user->business_id)
                ->where(function ($query) use ($productName) {
                    $query->whereRaw('LOWER(name) = ?', [strtolower($productName)])
                        ->orWhereRaw('LOWER(sku) = ?', [strtolower($productName)]);
                })
                ->first();

            if (! $product) {
                $reply = "Product '{$productName}' was not found in your business stock.";
            }
            else {
                DB::transaction(function () use ($user, $product, $quantity, $unitCost) {
                    $purchase = Purchase::create([
                        'business_id' => $user->business_id,
                        'shift_id' => Shift::where('business_id', $user->business_id)->where('user_id', $user->id)->where('status', 'open')->value('id'),
                        'user_id' => $user->id,
                        'supplier_name' => 'Telegram Bot',
                        'purchase_date' => now()->toDateString(),
                        'reference_no' => 'TG-PUR-' . now()->timestamp,
                        'notes' => 'Telegram bot purchase',
                        'total_amount' => 0,
                    ]);

                    $lineTotal = $quantity * $unitCost;
                    $purchase->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ]);

                    $product->stock_quantity = (int) $product->stock_quantity + $quantity;
                    $product->save();

                    $purchase->total_amount = $lineTotal;
                    $purchase->save();
                });

                $reply = "Purchase recorded successfully for {$quantity} {$product->name}.";
            }
        }
        elseif (preg_match('/^waste\s+(.+?)\s+(\d+)(?:\s+(.*))?$/i', $text, $matches)) {
            $productName = trim($matches[1]);
            $quantity = (int) $matches[2];
            $reason = trim($matches[3] ?: 'telegram');

            $product = Product::where('business_id', $user->business_id)
                ->where(function ($query) use ($productName) {
                    $query->whereRaw('LOWER(name) = ?', [strtolower($productName)])
                        ->orWhereRaw('LOWER(sku) = ?', [strtolower($productName)]);
                })
                ->first();

            if (! $product) {
                $reply = "Product '{$productName}' was not found in your business stock.";
            }
            elseif ($product->stock_quantity < $quantity) {
                $reply = "Waste quantity exceeds available stock for {$product->name}.";
            }
            else {
                DB::transaction(function () use ($user, $product, $quantity, $reason) {
                    WasteRecord::create([
                        'business_id' => $user->business_id,
                        'shift_id' => Shift::where('business_id', $user->business_id)->where('user_id', $user->id)->where('status', 'open')->value('id'),
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost_snapshot' => $product->default_cost,
                        'reason' => $reason,
                        'notes' => 'Telegram bot waste',
                        'recorded_at' => now(),
                    ]);

                    $product->stock_quantity = (int) $product->stock_quantity - $quantity;
                    $product->save();
                });

                $reply = "Waste recorded successfully for {$quantity} {$product->name}.";
            }
        }
        elseif (strtolower($text) === 'start shift') {
            $existing = Shift::where('business_id', $user->business_id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if ($existing) {
                $reply = 'You already have an open shift.';
            }
            else {
                $shift = Shift::create([
                    'business_id' => $user->business_id,
                    'user_id' => $user->id,
                    'shift_type' => 'morning',
                    'started_at' => now(),
                    'opening_cash' => 0,
                    'notes' => 'Started from Telegram',
                    'status' => 'open',
                ]);

                $reply = "Shift started successfully. Shift #{$shift->id} is now open.";
            }
        }
        elseif (strtolower($text) === 'close shift' || strtolower($text) === 'end shift') {
            $shift = Shift::where('business_id', $user->business_id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->first();

            if (! $shift) {
                $reply = 'There is no open shift for your account.';
            }
            else {
                $shift->update([
                    'closing_cash' => $shift->opening_cash,
                    'ended_at' => now(),
                    'status' => 'closed',
                    'notes' => ($shift->notes ? $shift->notes . ' | Closed from Telegram' : 'Closed from Telegram'),
                ]);

                $reply = 'Shift closed successfully.';
            }
        }
        elseif (strtolower($text) === 'today') {
            $salesTotal = Sale::where('business_id', $user->business_id)
                ->whereDate('sold_at', now()->toDateString())
                ->sum('total_amount');

            $reply = 'Today sales total: $' . number_format((float) $salesTotal, 2);
        }
        else {
            $reply = 'Unknown command. Use /help to see valid Telegram BizTrack commands.';
        }

        if ($token) {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $reply,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
