<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_number',
        'book_id',
        'quantity_requested',
        'quantity_fulfilled',
        'unit_price'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function getTotalRequestedValue()
    {
        return $this->quantity_requested * $this->unit_price;
    }

    public function getTotalFulfilledValue()
    {
        return $this->quantity_fulfilled * $this->unit_price;
    }

    public function isFulfilled()
    {
        return $this->quantity_fulfilled >= $this->quantity_requested;
    }

    public function isPartiallyFulfilled()
    {
        return $this->quantity_fulfilled > 0 && $this->quantity_fulfilled < $this->quantity_requested;
    }
}
