<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => $this->faker->word(),
            'sku' => strtoupper($this->faker->bothify('PROD-##??')),
            'unit' => 'pcs',
            'selling_price' => 3.50,
            'default_cost' => 1.25,
            'minimum_stock' => 5,
            'stock_quantity' => 20,
            'is_active' => true,
        ];
    }
}
