<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftOpeningStock extends Model
{
    use HasFactory;

    protected $table = 'shift_opening_stocks';

    protected $fillable = [
        'shift_id',
        'product_id',
        'quantity',
        'recorded_at',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
