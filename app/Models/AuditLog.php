<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'old_values',
        'new_values',
        'user_source',
        'user_identifier',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class, 'record_id');
    }

    // Helper method to get readable changes
    public function getReadableChanges()
    {
        $changes = [];

        // Fields to exclude from audit display
        $excludedFields = ['created_at', 'updated_at'];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $field => $newValue) {
                // Skip excluded fields
                if (in_array($field, $excludedFields)) {
                    continue;
                }
                
                $oldValue = $this->old_values[$field] ?? 'N/A';
                if ($oldValue != $newValue) {
                    $changes[] = [
                        'field' => ucfirst(str_replace('_', ' ', $field)),
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }

        return $changes;
    }
}
