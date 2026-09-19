<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $business = Business::firstOrCreate(
            ['name' => 'Demo Market'],
            [
                'business_type' => 'retail',
                'currency' => 'USD',
                'timezone' => 'Asia/Phnom_Penh',
                'status' => 'active',
            ]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@demo.com'],
            [
                'business_id' => $business->id,
                'name' => 'Demo Owner',
                'password' => Hash::make('demo12345'),
                'role' => 'owner',
            ]
        );

        $products = [
            ['name' => 'Milk', 'sku' => 'MILK-001', 'stock_quantity' => 30, 'default_cost' => 2.50, 'selling_price' => 3.50, 'unit' => 'bottle'],
            ['name' => 'Bread', 'sku' => 'BREAD-001', 'stock_quantity' => 20, 'default_cost' => 1.20, 'selling_price' => 2.00, 'unit' => 'loaf'],
            ['name' => 'Eggs', 'sku' => 'EGGS-001', 'stock_quantity' => 40, 'default_cost' => 0.80, 'selling_price' => 1.20, 'unit' => 'tray'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['business_id' => $business->id, 'sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'stock_quantity' => $product['stock_quantity'],
                    'default_cost' => $product['default_cost'],
                    'selling_price' => $product['selling_price'],
                    'unit' => $product['unit'],
                    'minimum_stock' => 5,
                    'is_active' => true,
                ]
            );
        }

        $owner->business()->associate($business);
    }
}
