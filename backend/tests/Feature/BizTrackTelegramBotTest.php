<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BizTrackTelegramBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_sale_command_updates_stock(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->create([
            'business_id' => $business->id,
            'role' => 'staff',
            'telegram_chat_id' => 123456,
        ]);

        $product = Product::factory()->create([
            'business_id' => $business->id,
            'name' => 'Milk',
            'sku' => 'MILK-01',
            'selling_price' => 3.50,
            'default_cost' => 1.20,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => [
                    'id' => 123456,
                    'type' => 'private',
                ],
                'text' => 'sale Milk 4 3.5',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sales', ['business_id' => $business->id]);
        $this->assertEquals(6, $product->fresh()->stock_quantity);
    }
}
