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

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function clientLimit(): int
    {
        if ($this->isAdmin()) {
            return -1;
        }

        if ($this->package) {
            return ((float) $this->package->price > 0 || $this->package->invoice_limit === -1) ? -1 : 10;
        }

        return 5;
    }

    public function canCreateClient(): bool
    {
        $limit = $this->clientLimit();
        if ($limit === -1) {
            return true;
        }

        return $this->clients()->count() < $limit;
    }

    public function hasFeature(string $feature): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->package && ((float) $this->package->price > 0 || $this->package->invoice_limit === -1)) {
            return true;
        }

        $freeFeatures = ['invoicing', 'quotations', 'client_portal', 'statements', 'time_tracking', 'expense_tracking'];

        return in_array($feature, $freeFeatures, true);
    }

    public function templatePurchases(): HasMany
    {
        return $this->hasMany(UserTemplatePurchase::class);
    }

    public function hasTemplateAccess(string $styleSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Default 4 core templates are always free for everyone
        if (in_array($styleSlug, ['minimalist', 'corporate', 'creative', 'grid'], true)) {
            return true;
        }

        $template = InvoiceTemplate::where('slug', $styleSlug)->first();
        if (! $template || ! $template->is_active) {
            return false;
        }

        if ($template->is_free) {
            return true;
        }

        return $this->templatePurchases()->where('template_id', $template->id)->exists();
    }

    public function ownedTemplateSlugs(): array
    {
        if ($this->isAdmin()) {
            return InvoiceTemplate::where('is_active', true)->pluck('slug')->toArray();
        }

        $freeSlugs = InvoiceTemplate::where('is_active', true)->where('is_free', true)->pluck('slug')->toArray();
        // Ensure default 4 are included
        $freeSlugs = array_unique(array_merge(['minimalist', 'corporate', 'creative', 'grid'], $freeSlugs));

        $purchasedSlugs = InvoiceTemplate::whereHas('purchases', function ($q) {
            $q->where('user_id', $this->id);
        })->where('is_active', true)->pluck('slug')->toArray();

        return array_values(array_unique(array_merge($freeSlugs, $purchasedSlugs)));
    }
}
