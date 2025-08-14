<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'book_id',
        'quantity',
        'unit_price',
        'total_price',
        'platform',
        'order_number',
        'buyer_info',
        'sale_date'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // Calculate total price from quantity and unit price
    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($sale) {
            $sale->total_price = $sale->quantity * $sale->unit_price;
        });
    }
}
