<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role check methods
    public function isSalesperson(): bool
    {
        return $this->role === 'salesperson';
    }

    public function isUnitHead(): bool
    {
        return $this->role === 'unit_head';
    }

    public function isStorekeeper(): bool
    {
        return $this->role === 'storekeeper';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canApproveOrders(): bool
    {
        return $this->isUnitHead() || $this->isAdmin();
    }

    public function canFulfillOrders(): bool
    {
        return $this->isStorekeeper() || $this->isAdmin();
    }

    public function canManageInventory(): bool
    {
        return $this->isStorekeeper() || $this->isAdmin();
    }

    // Relationships
    public function requestedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'requester_id');
    }

    public function approvedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'approver_id');
    }

    public function fulfilledOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'fulfiller_id');
    }
}
