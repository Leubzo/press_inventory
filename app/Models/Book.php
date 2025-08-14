<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn',
        'title',
        'authors_editors',
        'year',
        'pages',
        'price',
        'category',
        'other_category',
        'stock',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'price' => 'decimal:2',
        'year' => 'integer',
        'pages' => 'integer',
        'stock' => 'integer',
    ];
    
    /**
     * Boot method to register model events for audit logging
     */
    protected static function booted()
    {
        // Log when a book is created
        static::created(function ($book) {
            self::createAuditLog($book, 'created', null, $book->getAttributes());
        });
        
        // Log when a book is updated
        static::updated(function ($book) {
            // Only log if there are actual changes
            if ($book->isDirty()) {
                $changes = $book->getChanges();
                $original = $book->getOriginal();
                
                // Filter out timestamp changes from tracking
                unset($changes['updated_at']);
                unset($changes['created_at']);
                
                if (!empty($changes)) {
                    // Filter out timestamps from the audit data
                    $filteredOriginal = collect($original)->except(['created_at', 'updated_at'])->toArray();
                    $filteredNew = collect($book->getAttributes())->except(['created_at', 'updated_at'])->toArray();
                    
                    self::createAuditLog($book, 'updated', $filteredOriginal, $filteredNew);
                }
            }
        });
        
        // Log when a book is deleted
        static::deleting(function ($book) {
            self::createAuditLog($book, 'deleted', $book->getAttributes(), null);
        });
    }
    
    /**
     * Create an audit log entry
     */
    protected static function createAuditLog($book, $action, $oldValues = null, $newValues = null)
    {
        // Determine the source of the change
        $userSource = 'web'; // Default to web
        $userIdentifier = null;
        
        if (Auth::check()) {
            $userIdentifier = Auth::user()->email;
        } elseif (request()->header('X-AppSheet-User')) {
            // If the request comes from AppSheet
            $userSource = 'appsheet';
            $userIdentifier = request()->header('X-AppSheet-User');
        } elseif (request()->is('api/*')) {
            // If it's an API request
            $userSource = 'api';
            $userIdentifier = request()->ip();
        } else {
            $userIdentifier = request()->ip();
        }
        
        AuditLog::create([
            'table_name' => 'books',
            'record_id' => $book->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_source' => $userSource,
            'user_identifier' => $userIdentifier
        ]);
    }
    
    /**
     * Relationship with audit logs
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'record_id')
            ->where('table_name', 'books')
            ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get the latest audit log for this book
     */
    public function latestAuditLog()
    {
        return $this->hasOne(AuditLog::class, 'record_id')
            ->where('table_name', 'books')
            ->latest();
    }
    
    /**
     * Relationship with sales
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
    
    /**
     * Get total quantity sold for this book
     */
    public function getTotalSoldAttribute()
    {
        return $this->sales()->sum('quantity');
    }
    
    /**
     * Get total revenue from sales for this book
     */
    public function getTotalRevenueAttribute()
    {
        return $this->sales()->sum('total_price');
    }
    
    /**
     * Check if sales exceed current stock (needs attention)
     */
    public function getNeedsStockUpdateAttribute()
    {
        return $this->total_sold > $this->stock;
    }

    /**
     * Get the category attribute with consistent N/A display
     */
    public function getCategoryDisplayAttribute()
    {
        return empty($this->attributes['category']) ? 'N/A' : $this->attributes['category'];
    }

    /**
     * Get the other_category attribute with consistent N/A display
     */
    public function getOtherCategoryDisplayAttribute()
    {
        return empty($this->attributes['other_category']) ? 'N/A' : $this->attributes['other_category'];
    }
}