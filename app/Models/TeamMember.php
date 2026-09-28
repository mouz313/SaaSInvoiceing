<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'user_id',
        'role',
        'status',
    ];

    /**
     * Account owner user.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Invited member user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if member is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if member is accountant.
     */
    public function isAccountant(): bool
    {
        return in_array($this->role, ['admin', 'accountant'], true);
    }

    /**
     * Check if member is viewer.
     */
    public function isViewer(): bool
    {
        return in_array($this->role, ['admin', 'accountant', 'viewer'], true);
    }
}
