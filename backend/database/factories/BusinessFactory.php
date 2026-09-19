<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'business_type' => 'retail',
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'currency' => 'USD',
            'timezone' => 'Asia/Phnom_Penh',
            'status' => 'active',
        ];
    }
}
