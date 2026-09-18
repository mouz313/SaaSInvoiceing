<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'company_name',
    'email',
    'phone',
    'password',
    'firebase_uid',
    'role',
    'avatar_url',
    'address',
    'city',
    'country',
    'tax_id',
    'default_currency',
    'default_notes',
    'default_payment_instructions',
    'invoice_credits',
    'package_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'invoice_credits' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasCredits(): bool
    {
        // If user has a package with unlimited (-1) or credits > 0
        if ($this->package && $this->package->invoice_limit === -1) {
            return true;
        }

        return $this->invoice_credits > 0;
    }

    public function decrementCredits(): void
    {
        if ($this->package && $this->package->invoice_limit === -1) {
            return;
        }

        if ($this->invoice_credits > 0) {
            $this->decrement('invoice_credits');
        }
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function logos(): HasMany
    {
        return $this->hasMany(UserLogo::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }
}
