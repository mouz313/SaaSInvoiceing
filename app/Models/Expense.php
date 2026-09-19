<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'category',
        'description',
        'amount',
        'currency',
        'expense_date',
        'receipt_url',
        'is_billable',
        'is_billed',
        'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'is_billable' => 'boolean',
            'is_billed' => 'boolean',
        ];
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

    public function scopeBillable(Builder $query): Builder
    {
        return $query->where('is_billable', true);
    }
}
