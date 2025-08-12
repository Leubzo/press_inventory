<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    protected static function booted()
    {
        static::updated(function ($book) {
            AuditLog::create([
                'table_name' => 'books',
                'record_id' => $book->id,
                'action' => 'updated',
                'old_values' => $book->getOriginal(),
                'new_values' => $book->getAttributes(),
                'user_source' => 'system',
                'user_identifier' => Auth::check() ? Auth::user()->email : request()->ip()
            ]);
        });
    }
}
