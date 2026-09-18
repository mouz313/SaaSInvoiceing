<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Packages
        $starter = Package::create([
            'name' => 'Starter Plan',
            'slug' => 'starter',
            'description' => 'Ideal for freelancers and independent contractors.',
            'price' => 9.00,
            'billing_period' => 'monthly',
            'invoice_limit' => 25,
            'features' => [
                'Up to 25 Invoices per month',
                'All 4 Designer Layout Styles',
                'High-Resolution PDF Downloads',
                'Client Management Directory',
                'Standard Email Support',
            ],
            'stripe_price_id' => 'price_starter_monthly',
            'is_popular' => false,
            'is_active' => true,
        ]);

        $pro = Package::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Best for growing studios, agencies, and consulting teams.',
            'price' => 29.00,
            'billing_period' => 'monthly',
            'invoice_limit' => 100,
            'features' => [
                'Up to 100 Invoices per month',
                'All 4 Designer Layout Styles',
                'Auto Tax & Multi-Currency Engine',
                'Client Portal & Status Tracking',
                'Priority Processing & Export',
                'Direct Stripe Checkout Integration',
            ],
            'stripe_price_id' => 'price_pro_monthly',
            'is_popular' => true,
            'is_active' => true,
        ]);

        $enterprise = Package::create([
            'name' => 'Enterprise Unlimited',
            'slug' => 'enterprise',
            'description' => 'Uncapped volume for scaling businesses and agencies.',
            'price' => 79.00,
            'billing_period' => 'monthly',
            'invoice_limit' => -1, // Unlimited
            'features' => [
                'Unlimited Invoices Every Month',
                'All 4 Designer Layout Styles',
                'Custom Watermarks & Signatures',
                'Unlimited Client Profiles',
                'Full Audit Logs & Webhooks',
                '24/7 Dedicated Account Manager',
            ],
            'stripe_price_id' => 'price_enterprise_monthly',
            'is_popular' => false,
            'is_active' => true,
        ]);

        // 2. Seed Super Administrator
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@invoicehub.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'invoice_credits' => 9999,
            'package_id' => $enterprise->id,
        ]);

        // 3. Seed Demo User
        $user = User::create([
            'name' => 'Alex Rivera (Studio)',
            'email' => 'demo@invoicehub.test',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'invoice_credits' => 45,
            'package_id' => $pro->id,
        ]);

        // 4. Seed Demo Clients for User
        $client1 = Client::create([
            'user_id' => $user->id,
            'name' => 'Sarah Jenkins',
            'company_name' => 'Acme Technologies Inc',
            'email' => 'sarah@acme.io',
            'phone' => '+1 (415) 890-1234',
            'address' => '500 Howard Street, Suite 400',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'country' => 'United States',
            'tax_id' => 'US-89102941',
        ]);

        $client2 = Client::create([
            'user_id' => $user->id,
            'name' => 'Marcus Vance',
            'company_name' => 'Vanguard Global Advisory',
            'email' => 'marcus@vanguard.co.uk',
            'phone' => '+44 20 7946 0991',
            'address' => '25 Bank Street, Canary Wharf',
            'city' => 'London',
            'state' => 'England',
            'postal_code' => 'E14 5JP',
            'country' => 'United Kingdom',
            'tax_id' => 'GB-48192049',
        ]);

        $client3 = Client::create([
            'user_id' => $user->id,
            'name' => 'Elena Rostova',
            'company_name' => 'HyperMotion Animation Labs',
            'email' => 'elena@hypermotion.tv',
            'phone' => '+1 (212) 555-0199',
            'address' => '120 Broadway, 18th Floor',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10271',
            'country' => 'United States',
            'tax_id' => 'US-77301928',
        ]);

        // 5. Seed Invoices Across All 4 Styles
        // Invoice 1: Minimalist Style (Paid)
        $inv1 = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client1->id,
            'invoice_number' => 'INV-2026-0001',
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(4),
            'status' => 'paid',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 4500.00,
            'tax_rate' => 8.50,
            'tax_amount' => 382.50,
            'discount_rate' => 0.00,
            'discount_amount' => 0.00,
            'total' => 4882.50,
            'notes' => 'Thank you for partnering with us on the Q3 website overhaul.',
            'payment_instructions' => 'Settled via Stripe Checkout on Sep 05, 2026.',
        ]);
        $inv1->items()->createMany([
            ['description' => 'Custom UI/UX Design System & Figma Kit', 'quantity' => 1, 'unit_price' => 2000.00, 'amount' => 2000.00],
            ['description' => 'Responsive Web Application Frontend Build', 'quantity' => 1, 'unit_price' => 2500.00, 'amount' => 2500.00],
        ]);

        // Invoice 2: Corporate Classic Style (Sent)
        $inv2 = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client2->id,
            'invoice_number' => 'INV-2026-0002',
            'invoice_date' => now()->subDays(5),
            'due_date' => now()->addDays(25),
            'status' => 'sent',
            'style' => 'corporate',
            'currency' => 'USD',
            'subtotal' => 6000.00,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'discount_rate' => 5.00,
            'discount_amount' => 300.00,
            'total' => 5700.00,
            'notes' => 'Official audit and technical advisory retainer for Q3.',
            'payment_instructions' => 'Wire transfer: Chase Bank, Account: 9812-4019, Routing: 021000021.',
        ]);
        $inv2->items()->createMany([
            ['description' => 'Enterprise Architecture & Cloud Security Audit', 'quantity' => 1, 'unit_price' => 4000.00, 'amount' => 4000.00],
            ['description' => 'Performance Optimization Sprint', 'quantity' => 1, 'unit_price' => 2000.00, 'amount' => 2000.00],
        ]);

        // Invoice 3: Creative Bold Style (Paid)
        $inv3 = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client3->id,
            'invoice_number' => 'INV-2026-0003',
            'invoice_date' => now()->subDays(2),
            'due_date' => now()->addDays(12),
            'status' => 'paid',
            'style' => 'creative',
            'currency' => 'USD',
            'subtotal' => 3200.00,
            'tax_rate' => 5.00,
            'tax_amount' => 160.00,
            'discount_rate' => 0.00,
            'discount_amount' => 0.00,
            'total' => 3360.00,
            'notes' => 'Delivered in full resolution ProRes 4444 and master web assets.',
            'payment_instructions' => 'Payment received in full. Thank you!',
        ]);
        $inv3->items()->createMany([
            ['description' => '3D Product Visualization & Animation Loop', 'quantity' => 1, 'unit_price' => 3200.00, 'amount' => 3200.00],
        ]);

        // Invoice 4: Clean Grid Style (Draft)
        $inv4 = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client1->id,
            'invoice_number' => 'INV-2026-0004',
            'invoice_date' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'draft',
            'style' => 'grid',
            'currency' => 'USD',
            'subtotal' => 1850.00,
            'tax_rate' => 8.50,
            'tax_amount' => 157.25,
            'discount_rate' => 0.00,
            'discount_amount' => 0.00,
            'total' => 2007.25,
            'notes' => 'Draft invoice under review.',
            'payment_instructions' => 'Net 14 payment terms apply upon final issuance.',
        ]);
        $inv4->items()->createMany([
            ['description' => 'Monthly Maintenance & DevOps Retainer', 'quantity' => 1, 'unit_price' => 1850.00, 'amount' => 1850.00],
        ]);

        // 6. Record sample billing transaction
        Transaction::create([
            'user_id' => $user->id,
            'package_id' => $pro->id,
            'stripe_session_id' => 'cs_test_demo_sample_01',
            'amount' => 29.00,
            'currency' => 'usd',
            'status' => 'completed',
        ]);
    }
}
