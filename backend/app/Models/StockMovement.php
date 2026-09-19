<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'product_id',
        'shift_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity_in',
        'quantity_out',
        'unit_cost',
        'occurred_at',
        'created_by',
    ];

    protected $casts = [
        'quantity_in' => 'integer',
        'quantity_out' => 'integer',
        'unit_cost' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
