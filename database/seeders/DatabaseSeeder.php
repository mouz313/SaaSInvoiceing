<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Coupon;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Package;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\RecurringInvoice;
use App\Models\TeamMember;
use App\Models\TimeEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTemplatePurchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete, production-ready demo data.
     */
    public function run(): void
    {
        // 1. Seed CMS Pages, FAQ, and Global Settings
        $this->call(CmsAndSettingsSeeder::class);

        // 2. Seed All 25 Designer Invoice Templates
        $this->call(InvoiceTemplateSeeder::class);

        // 3. Seed Subscription Packages
        $starter = Package::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter Plan',
                'description' => 'Ideal for freelancers and independent contractors.',
                'price' => 9.00,
                'billing_period' => 'monthly',
                'invoice_limit' => 25,
                'features' => [
                    'Up to 25 Invoices per month',
                    'All 25 Designer Layout Styles',
                    'High-Resolution PDF Downloads',
                    'Client Management Directory',
                    'Standard Email Support',
                ],
                'stripe_price_id' => 'price_starter_monthly',
                'is_popular' => false,
                'is_active' => true,
            ]
        );

        $pro = Package::firstOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Best for growing studios, agencies, and consulting teams.',
                'price' => 29.00,
                'billing_period' => 'monthly',
                'invoice_limit' => 100,
                'features' => [
                    'Up to 100 Invoices per month',
                    'All 25 Designer Layout Styles',
                    'FBR Digital Invoicing & POS QR Codes',
                    'Promotional Coupons & Discount Engine',
                    'Client Portal & Auto Reminders',
                    'Bank IBFT, Raast, JazzCash & EasyPaisa',
                    'Direct Stripe Card Checkout',
                ],
                'stripe_price_id' => 'price_pro_monthly',
                'is_popular' => true,
                'is_active' => true,
            ]
        );

        $enterprise = Package::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise Unlimited',
                'description' => 'Uncapped volume for scaling businesses and agencies.',
                'price' => 79.00,
                'billing_period' => 'monthly',
                'invoice_limit' => -1, // Unlimited
                'features' => [
                    'Unlimited Invoices Every Month',
                    'All 25 Designer Layout Styles',
                    'Full FBR EBS Digital Invoicing',
                    'Multi-User Team Roles & Permissions',
                    'Automated Recurring Billing',
                    'Priority 24/7 Dedicated Support',
                ],
                'stripe_price_id' => 'price_enterprise_monthly',
                'is_popular' => false,
                'is_active' => true,
            ]
        );

        // 4. Seed Super Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@invoicehub.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'invoice_credits' => 9999,
                'package_id' => $enterprise->id,
                'email_verified_at' => now(),
            ]
        );

        // 5. Seed Demo Business User (Studio & Invoicing Merchant)
        $user = User::firstOrCreate(
            ['email' => 'demo@invoicehub.test'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'company_name' => 'Nexus Digital Studio',
                'phone' => '+92 300 1234567',
                'address' => 'Suite 402, Business Bay, Gulberg III',
                'city' => 'Lahore',
                'country' => 'Pakistan',
                'tax_id' => 'PK-7294012-3',
                'ntn' => '7294012-3',
                'strn' => '3277876123456',
                'cnic' => '3520112345671',
                'default_currency' => 'PKR',
                'default_notes' => 'Thank you for choosing Nexus Digital Studio! All deliverables include 30-day technical warranty.',
                'default_payment_instructions' => 'Please remit payment via Bank Transfer (IBFT) or Raast ID within invoice terms.',
                'bank_name' => 'Meezan Bank',
                'bank_account_title' => 'Nexus Digital Studio',
                'bank_account_number' => '01010101928374',
                'bank_iban' => 'PK36MEZN0001010192837401',
                'raast_id' => '03001234567',
                'jazzcash_number' => '03001234567',
                'jazzcash_title' => 'Nexus Digital',
                'easypaisa_number' => '03451234567',
                'easypaisa_title' => 'Nexus Digital',
                'fbr_enabled' => true,
                'fbr_environment' => 'sandbox',
                'fbr_pos_id' => '102938',
                'fbr_pos_usin' => 'POS-NEXUS-01',
                'fbr_bearer_token' => 'fbr_sandbox_demo_token_secure',
                'invoice_credits' => 100,
                'package_id' => $pro->id,
                'email_verified_at' => now(),
            ]
        );

        // Grant Demo User Access to all 25 Templates
        $allTemplates = InvoiceTemplate::where('is_active', true)->get();
        foreach ($allTemplates as $template) {
            UserTemplatePurchase::firstOrCreate(
                ['user_id' => $user->id, 'template_id' => $template->id],
                [
                    'price_paid' => $template->price,
                    'currency' => 'USD',
                    'payment_method' => 'included_plan',
                    'transaction_id' => 'INIT-PLAN-'.$template->id,
                    'purchased_at' => now(),
                ]
            );
        }

        // 6. Seed Demo Clients
        $client1 = Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => 'billing@systemstech.pk'],
            [
                'name' => 'Tariq Mahmood',
                'company_name' => 'Systems Tech Pvt Ltd',
                'phone' => '+92 300 9876543',
                'address' => 'Floor 8, Arfa Software Technology Park, Ferozepur Road',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'postal_code' => '54600',
                'country' => 'Pakistan',
                'ntn' => '4192049-1',
                'strn' => '3277876123456',
                'cnic' => '3520112345671',
                'currency' => 'PKR',
                'password' => Hash::make('password123'),
                'portal_access_token' => Str::random(32),
            ]
        );

        $client2 = Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => 'sarah@acme.io'],
            [
                'name' => 'Sarah Jenkins',
                'company_name' => 'Acme Technologies Inc',
                'phone' => '+1 (415) 890-1234',
                'address' => '500 Howard Street, Suite 400',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94105',
                'country' => 'United States',
                'tax_id' => 'US-89102941',
                'currency' => 'USD',
                'password' => Hash::make('password123'),
                'portal_access_token' => Str::random(32),
            ]
        );

        $client3 = Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => 'marcus@vanguard.co.uk'],
            [
                'name' => 'Marcus Vance',
                'company_name' => 'Vanguard Global Advisory',
                'phone' => '+44 20 7946 0991',
                'address' => '25 Bank Street, Canary Wharf',
                'city' => 'London',
                'state' => 'England',
                'postal_code' => 'E14 5JP',
                'country' => 'United Kingdom',
                'tax_id' => 'GB-48192049',
                'currency' => 'GBP',
                'password' => Hash::make('password123'),
                'portal_access_token' => Str::random(32),
            ]
        );

        $client4 = Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => 'elena@hypermotion.tv'],
            [
                'name' => 'Elena Rostova',
                'company_name' => 'HyperMotion Animation Labs',
                'phone' => '+1 (212) 555-0199',
                'address' => '120 Broadway, 18th Floor',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10271',
                'country' => 'United States',
                'tax_id' => 'US-77301928',
                'currency' => 'USD',
                'password' => Hash::make('password123'),
                'portal_access_token' => Str::random(32),
            ]
        );

        // 7. Seed Product Categories & Catalog Items
        $catDev = ProductCategory::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Software & Engineering'],
            ['color' => 'blue']
        );
        $catDesign = ProductCategory::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Design & Creative'],
            ['color' => 'purple']
        );
        $catConsulting = ProductCategory::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Cloud & Architecture Consulting'],
            ['color' => 'emerald']
        );

        Product::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Custom SaaS Web Application Build'],
            [
                'category_id' => $catDev->id,
                'description' => 'Full-stack application development sprint including API design and database schema.',
                'price' => 150000.00,
                'unit' => 'project',
                'sku' => 'DEV-SAAS-01',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'UI/UX Design System & Figma Master'],
            [
                'category_id' => $catDesign->id,
                'description' => 'Comprehensive multi-breakpoint design system with interactive prototypes.',
                'price' => 85000.00,
                'unit' => 'package',
                'sku' => 'DSN-FGM-01',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Cloud Infrastructure & DevOps Setup'],
            [
                'category_id' => $catConsulting->id,
                'description' => 'Containerized Docker/K8s deployment with automated CI/CD and SSL encryption.',
                'price' => 65000.00,
                'unit' => 'setup',
                'sku' => 'OPS-CLD-01',
                'is_active' => true,
            ]
        );

        // 8. Seed Promotional Coupons & Discounts
        $couponWelcome = Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'user_id' => $user->id,
                'name' => 'Welcome 10% Discount',
                'description' => 'Special 10% discount for first-time invoice settlements.',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'applies_to' => 'invoices',
                'min_spend' => 1000.00,
                'max_discount' => 20000.00,
                'max_uses' => 100,
                'times_used' => 4,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'FLAT500'],
            [
                'user_id' => $user->id,
                'name' => 'Flat 500 Discount Voucher',
                'description' => 'Instant flat discount on any invoiced project.',
                'discount_type' => 'fixed',
                'discount_value' => 500.00,
                'applies_to' => 'invoices',
                'min_spend' => 2000.00,
                'max_uses' => 50,
                'times_used' => 1,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'VIPCLIENT'],
            [
                'user_id' => $user->id,
                'client_id' => $client1->id,
                'name' => 'VIP Corporate Retainer Partner',
                'description' => 'Exclusive 20% discount reserved for Systems Tech.',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'applies_to' => 'invoices',
                'min_spend' => 50000.00,
                'max_discount' => 50000.00,
                'max_uses' => 10,
                'times_used' => 0,
                'is_active' => true,
            ]
        );

        // 9. Seed Invoices Across All Key Styles and Statuses

        // Invoice 1: Minimalist Style — Fully Paid with Payment Ledger (PKR)
        $inv1 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0001'],
            [
                'user_id' => $user->id,
                'client_id' => $client1->id,
                'invoice_date' => now()->subDays(12),
                'due_date' => now()->subDays(2),
                'status' => 'paid',
                'style' => 'minimalist',
                'currency' => 'PKR',
                'subtotal' => 150000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 27000.00,
                'tax_authority' => 'Punjab Revenue Authority (PRA)',
                'discount_rate' => 0.00,
                'discount_amount' => 0.00,
                'total' => 177000.00,
                'amount_paid' => 177000.00,
                'balance_due' => 0.00,
                'notes' => 'Q3 Enterprise Software Development Milestone.',
                'payment_instructions' => 'Settled via Meezan Bank IBFT.',
            ]
        );
        if ($inv1->items()->count() === 0) {
            $inv1->items()->createMany([
                ['description' => 'Custom SaaS Web Application Build Sprint', 'quantity' => 1, 'unit_price' => 150000.00, 'amount' => 150000.00],
            ]);
        }
        if ($inv1->payments()->count() === 0) {
            $inv1->payments()->create([
                'user_id' => $user->id,
                'amount' => 177000.00,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'MEZN-TX-901824',
                'paid_at' => now()->subDays(3),
                'status' => 'completed',
                'notes' => 'Verified direct IBFT wire received in business bank account.',
            ]);
        }

        // Invoice 2: Corporate Classic — FBR IMS Fiscalized & QR Code (PKR)
        $inv2 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0002'],
            [
                'user_id' => $user->id,
                'client_id' => $client1->id,
                'invoice_date' => now()->subDays(4),
                'due_date' => now()->addDays(14),
                'status' => 'sent',
                'style' => 'corporate',
                'currency' => 'PKR',
                'subtotal' => 85000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 15300.00,
                'tax_authority' => 'Federal Board of Revenue (FBR)',
                'pct_code' => '9801.0000',
                'discount_rate' => 0.00,
                'discount_amount' => 0.00,
                'total' => 100300.00,
                'amount_paid' => 0.00,
                'balance_due' => 100300.00,
                'fbr_status' => 'synced',
                'fbr_invoice_number' => '102938202600000002',
                'fbr_qr_code_data' => 'FBR:102938202600000002:POS:102938:TIME:'.now()->subDays(4)->format('Y-m-d H:i:s').':AMT:100300.00:NTN:4192049-1',
                'fbr_synced_at' => now()->subDays(4),
                'notes' => 'FBR Digital Invoicing & POS integrated compliance invoice.',
                'payment_instructions' => 'Payable via Raast ID: 03001234567 or Meezan Bank IBFT.',
            ]
        );
        if ($inv2->items()->count() === 0) {
            $inv2->items()->createMany([
                ['description' => 'Cloud Infrastructure & DevOps Security Setup', 'quantity' => 1, 'unit_price' => 85000.00, 'amount' => 85000.00],
            ]);
        }

        // Invoice 3: Creative Studio — Applied Coupon Code Discount (USD)
        $inv3 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0003'],
            [
                'user_id' => $user->id,
                'client_id' => $client2->id,
                'invoice_date' => now()->subDays(2),
                'due_date' => now()->addDays(12),
                'status' => 'sent',
                'style' => 'creative',
                'currency' => 'USD',
                'subtotal' => 3200.00,
                'tax_rate' => 0.00,
                'tax_amount' => 0.00,
                'coupon_id' => $couponWelcome->id,
                'coupon_code' => 'WELCOME10',
                'discount_rate' => 10.00,
                'discount_amount' => 320.00,
                'total' => 2880.00,
                'amount_paid' => 0.00,
                'balance_due' => 2880.00,
                'notes' => 'Interactive UI/UX Prototypes & Figma Deliverables.',
                'payment_instructions' => 'Payable online via credit or debit card.',
            ]
        );
        if ($inv3->items()->count() === 0) {
            $inv3->items()->createMany([
                ['description' => 'Comprehensive Design System & Mobile App Prototypes', 'quantity' => 1, 'unit_price' => 3200.00, 'amount' => 3200.00],
            ]);
        }

        // Invoice 4: Clean Grid — Partially Paid with Pending Proof for Demo Verification (USD)
        $inv4 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0004'],
            [
                'user_id' => $user->id,
                'client_id' => $client3->id,
                'invoice_date' => now()->subDays(6),
                'due_date' => now()->addDays(8),
                'status' => 'partially_paid',
                'style' => 'grid',
                'currency' => 'USD',
                'subtotal' => 5000.00,
                'tax_rate' => 0.00,
                'tax_amount' => 0.00,
                'discount_rate' => 0.00,
                'discount_amount' => 0.00,
                'total' => 5000.00,
                'amount_paid' => 2000.00,
                'balance_due' => 3000.00,
                'notes' => 'Enterprise Architecture & Cloud Security Audit Retainer.',
                'payment_instructions' => 'Wire transfer to London clearing bank.',
            ]
        );
        if ($inv4->items()->count() === 0) {
            $inv4->items()->createMany([
                ['description' => 'Quarterly Cyber Security Audit & Penetration Testing', 'quantity' => 1, 'unit_price' => 5000.00, 'amount' => 5000.00],
            ]);
        }
        if ($inv4->payments()->count() === 0) {
            $inv4->payments()->create([
                'user_id' => $user->id,
                'amount' => 2000.00,
                'payment_method' => 'stripe_checkout',
                'reference_number' => 'ch_3PqL8k9182941',
                'paid_at' => now()->subDays(4),
                'status' => 'completed',
                'notes' => 'Partial installment settled via Stripe.',
            ]);
            $inv4->payments()->create([
                'user_id' => $user->id,
                'amount' => 1500.00,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'WIRE-UK-77192',
                'paid_at' => now()->subHour(),
                'status' => 'pending_verification',
                'notes' => 'Client uploaded wire transfer confirmation slip. Pending merchant review.',
            ]);
        }

        // Invoice 5: Executive Slate — Draft (PKR)
        $inv5 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0005'],
            [
                'user_id' => $user->id,
                'client_id' => $client1->id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(20),
                'status' => 'draft',
                'style' => 'executive_dark',
                'currency' => 'PKR',
                'subtotal' => 60000.00,
                'tax_rate' => 18.00,
                'tax_amount' => 10800.00,
                'total' => 70800.00,
                'amount_paid' => 0.00,
                'balance_due' => 70800.00,
                'notes' => 'Draft invoice under internal review before transmission.',
            ]
        );
        if ($inv5->items()->count() === 0) {
            $inv5->items()->createMany([
                ['description' => 'Dedicated Monthly Database Administrator (DBA) Support', 'quantity' => 1, 'unit_price' => 60000.00, 'amount' => 60000.00],
            ]);
        }

        // Invoice 6: Overdue Invoice (USD)
        $inv6 = Invoice::firstOrCreate(
            ['invoice_number' => 'INV-2026-0006'],
            [
                'user_id' => $user->id,
                'client_id' => $client4->id,
                'invoice_date' => now()->subDays(35),
                'due_date' => now()->subDays(10),
                'status' => 'overdue',
                'style' => 'corporate',
                'currency' => 'USD',
                'subtotal' => 1800.00,
                'tax_rate' => 0.00,
                'tax_amount' => 0.00,
                'total' => 1800.00,
                'amount_paid' => 0.00,
                'balance_due' => 1800.00,
                'notes' => 'Past due invoice. Please settle immediately.',
            ]
        );
        if ($inv6->items()->count() === 0) {
            $inv6->items()->createMany([
                ['description' => 'Interactive 3D WebGL Animation Reel', 'quantity' => 1, 'unit_price' => 1800.00, 'amount' => 1800.00],
            ]);
        }

        // 10. Seed Quotations / Estimates
        $est1 = Estimate::firstOrCreate(
            ['estimate_number' => 'EST-2026-0001'],
            [
                'user_id' => $user->id,
                'client_id' => $client2->id,
                'estimate_date' => now()->subDays(2),
                'expiry_date' => now()->addDays(20),
                'status' => 'sent',
                'style' => 'corporate',
                'currency' => 'USD',
                'subtotal' => 4500.00,
                'tax_rate' => 0.00,
                'tax_amount' => 0.00,
                'total' => 4500.00,
                'notes' => 'Proposal for modernizing client infrastructure.',
                'terms' => 'Estimate valid for 30 days from date of issue.',
                'public_token' => Str::random(32),
            ]
        );
        if ($est1->items()->count() === 0) {
            $est1->items()->createMany([
                ['description' => 'Kubernetes Microservices Architecture Migration', 'quantity' => 1, 'unit_price' => 4500.00, 'amount' => 4500.00],
            ]);
        }

        // 11. Seed Recurring Invoices Profile
        RecurringInvoice::firstOrCreate(
            ['user_id' => $user->id, 'client_id' => $client1->id],
            [
                'title' => 'Monthly Cloud Maintenance & SLA Retainer',
                'frequency' => 'monthly',
                'currency' => 'PKR',
                'style' => 'minimalist',
                'status' => 'active',
                'start_date' => now()->startOfMonth(),
                'next_issue_date' => now()->addMonth()->startOfMonth(),
                'tax_rate' => 18.00,
                'subtotal' => 65000.00,
                'total' => 76700.00,
                'auto_send_email' => true,
            ]
        );

        // 12. Seed Time Entries & Unbilled Expenses for Invoice Creator
        TimeEntry::firstOrCreate(
            ['user_id' => $user->id, 'task_description' => 'REST API Architecture Review & Security Hardening'],
            [
                'client_id' => $client1->id,
                'project_name' => 'Nexus Cloud Platform',
                'hours' => 6.5,
                'hourly_rate' => 5000.00,
                'total_amount' => 32500.00,
                'date' => now()->subDays(2),
                'is_billed' => false,
            ]
        );

        Expense::firstOrCreate(
            ['user_id' => $user->id, 'description' => 'Cloudflare Enterprise SSL & CDN Domain Setup'],
            [
                'client_id' => $client1->id,
                'category' => 'Infrastructure',
                'amount' => 12500.00,
                'currency' => 'PKR',
                'expense_date' => now()->subDays(3),
                'is_billable' => true,
                'is_billed' => false,
            ]
        );

        // 13. Seed Team Member (Demonstrating Multi-User / Role-Switching)
        $teamUser = User::firstOrCreate(
            ['email' => 'accountant@invoicehub.test'],
            [
                'name' => 'Zain Malik (Accountant)',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'invoice_credits' => 10,
                'email_verified_at' => now(),
            ]
        );

        TeamMember::firstOrCreate(
            ['owner_id' => $user->id, 'user_id' => $teamUser->id],
            [
                'role' => 'accountant',
                'status' => 'active',
            ]
        );

        // 14. Record sample transaction for subscription analytics
        Transaction::firstOrCreate(
            ['stripe_session_id' => 'cs_test_demo_sample_01'],
            [
                'user_id' => $user->id,
                'package_id' => $pro->id,
                'amount' => 29.00,
                'currency' => 'usd',
                'status' => 'completed',
            ]
        );
    }
}
