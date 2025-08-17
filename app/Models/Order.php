<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'items_count',
        'status',
        'purpose',
        'platform',
        'requester_id',
        'approver_id',
        'fulfiller_id',
        'notes',
        'approval_date',
        'fulfillment_date'
    ];

    protected $casts = [
        'approval_date' => 'datetime',
        'fulfillment_date' => 'datetime'
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function fulfiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfiller_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('item_number');
    }

    public function calculateTotalValue()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->quantity_requested * $item->unit_price;
        });
    }

    public function updateItemsCount()
    {
        $this->update(['items_count' => $this->orderItems()->count()]);
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . date('Y') . '-';
        
        // Get the highest order number for this year
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();
        
        if (!$lastOrder) {
            return $prefix . '001';
        }
        
        // Extract the number part after the last dash
        $lastOrderNumber = $lastOrder->order_number;
        $lastDashPos = strrpos($lastOrderNumber, '-');
        $lastNumber = (int) substr($lastOrderNumber, $lastDashPos + 1);
        
        $nextNumber = $lastNumber + 1;
        
        // Use dynamic padding based on number size:
        // 1-999: 3 digits (001-999)
        // 1000-9999: 4 digits (1000-9999)  
        // 10000+: 5+ digits (10000+)
        if ($nextNumber < 1000) {
            return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } elseif ($nextNumber < 10000) {
            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        } else {
            return $prefix . $nextNumber; // No padding for 10000+
        }
    }
}
