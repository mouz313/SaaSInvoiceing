<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'project_name',
        'task_description',
        'hours',
        'hourly_rate',
        'total_amount',
        'date',
        'is_billed',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_billed' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TimeEntry $entry) {
            $entry->total_amount = round(((float) $entry->hours) * ((float) $entry->hourly_rate), 2);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->where('is_billed', false);
    }
}
