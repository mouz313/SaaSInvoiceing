<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserLogo extends Model
{
    public const MAX_LOGOS_PER_USER = 10;

    protected $fillable = [
        'user_id',
        'filename',
        'path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the public URL for the logo.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Get the absolute filesystem path for PDF embedding.
     */
    public function getAbsolutePathAttribute(): string
    {
        return storage_path('app/public/'.$this->path);
    }
}
