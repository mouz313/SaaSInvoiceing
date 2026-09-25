<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'price',
        'currency',
        'is_free',
        'is_active',
        'preview_image',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(UserTemplatePurchase::class, 'template_id');
    }

    public function isOwnedBy(?User $user): bool
    {
        if ($this->is_free) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->purchases()->where('user_id', $user->id)->exists();
    }

    /**
     * Build an in-memory sample invoice for modal previews without DB queries.
     */
    public static function sampleInvoice(?User $user = null): Invoice
    {
        $mockUser = (object) [
            'name' => $user?->name ?? 'Apex Creative Agency',
            'company_name' => $user?->company_name ?? 'Apex Creative Studio LLC',
            'email' => $user?->email ?? 'billing@apexstudio.design',
        ];

        $mockClient = new Client([
            'name' => 'Nexus Global Technologies',
            'company_name' => 'Nexus Global Inc.',
            'address' => '742 Evergreen Suite 400',
            'city' => 'San Francisco',
            'country' => 'United States',
            'email' => 'finance@nexusglobal.com',
        ]);

        $mockInvoice = new Invoice([
            'invoice_number' => 'INV-SAMPLE-2026',
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 2850.00,
            'discount_rate' => 5.00,
            'discount_amount' => 142.50,
            'tax_rate' => 10.00,
            'tax_amount' => 270.75,
            'total' => 2978.25,
            'amount_paid' => 0.00,
            'balance_due' => 2978.25,
            'notes' => 'Thank you for your business. All digital deliverable source files are provided upon full settlement.',
            'payment_instructions' => 'Wire Transfer to Chase Bank (Account: 987654321, Routing: 021000021) or pay via Stripe portal.',
        ]);

        $mockInvoice->setRelation('user', $mockUser);
        $mockInvoice->setRelation('client', $mockClient);
        $mockInvoice->setRelation('logo', null);

        $mockItems = collect([
            new InvoiceItem(['description' => 'Enterprise UI/UX Architecture & Prototyping', 'quantity' => 1, 'unit_price' => 1400.00, 'amount' => 1400.00]),
            new InvoiceItem(['description' => 'Full-Stack API Integration & Microservices', 'quantity' => 24, 'unit_price' => 50.00, 'amount' => 1200.00]),
            new InvoiceItem(['description' => 'Cloud Deployment & Automated CI/CD Pipeline', 'quantity' => 1, 'unit_price' => 250.00, 'amount' => 250.00]),
        ]);

        $mockInvoice->setRelation('items', $mockItems);

        return $mockInvoice;
    }
}
