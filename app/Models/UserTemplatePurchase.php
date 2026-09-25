<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTemplatePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'price_paid',
        'currency',
        'payment_method',
        'transaction_id',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'purchased_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'template_id');
    }
}
