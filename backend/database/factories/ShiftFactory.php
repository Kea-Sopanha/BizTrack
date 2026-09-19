<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'shift_type' => 'morning',
            'started_at' => now()->subHours(4),
            'ended_at' => null,
            'opening_cash' => 100.00,
            'closing_cash' => null,
            'notes' => 'Factory generated shift',
            'status' => 'open',
        ];
    }
}
