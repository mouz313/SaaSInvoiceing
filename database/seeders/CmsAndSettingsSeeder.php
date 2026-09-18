<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CmsAndSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. System Settings
        $settings = [
            // General & Branding
            ['key' => 'app_name', 'value' => 'InvoiceHub', 'group' => 'general', 'type' => 'string'],
            ['key' => 'support_email', 'value' => 'support@invoicehub.test', 'group' => 'general', 'type' => 'string'],
            ['key' => 'support_phone', 'value' => '+1 (800) 555-0199', 'group' => 'general', 'type' => 'string'],
            ['key' => 'office_address', 'value' => '100 Market St, Suite 400, San Francisco, CA 94105', 'group' => 'general', 'type' => 'string'],
            ['key' => 'copyright_text', 'value' => '© 2026 InvoiceHub Inc. All rights reserved.', 'group' => 'general', 'type' => 'string'],

            // Social Media Links
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/invoicehub', 'group' => 'social', 'type' => 'string'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/invoicehub', 'group' => 'social', 'type' => 'string'],
            ['key' => 'social_github', 'value' => 'https://github.com/invoicehub', 'group' => 'social', 'type' => 'string'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/invoicehub', 'group' => 'social', 'type' => 'string'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/invoicehub', 'group' => 'social', 'type' => 'string'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@invoicehub', 'group' => 'social', 'type' => 'string'],

            // Stripe Gateway
            ['key' => 'stripe_mode', 'value' => 'test', 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_publishable_key', 'value' => '', 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_secret_key', 'value' => '', 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_webhook_secret', 'value' => '', 'group' => 'stripe', 'type' => 'string'],
            ['key' => 'stripe_currency', 'value' => 'usd', 'group' => 'stripe', 'type' => 'string'],

            // Firebase Authentication
            ['key' => 'firebase_api_key', 'value' => '', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_auth_domain', 'value' => '', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_project_id', 'value' => '', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_storage_bucket', 'value' => '', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_messaging_sender_id', 'value' => '521548677158', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_app_id', 'value' => '1:521548677158:web:1e68ae511251c2ec2de6e7', 'group' => 'firebase', 'type' => 'string'],
            ['key' => 'firebase_measurement_id', 'value' => 'G-M54BP64R8Q', 'group' => 'firebase', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. CMS Pages
        CmsPage::updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'Empowering Modern Businesses to Get Paid Faster & Look Impeccable',
                'subtitle' => 'InvoiceHub was built to liberate freelancers, agencies, and hyper-growth founders from ugly, clunky legacy accounting tools.',
                'meta_description' => 'Discover our mission, story, principles, and team at InvoiceHub - the premier modern SaaS invoicing platform.',
                'content' => [
                    'mission_statement' => 'We believe invoicing should be as enjoyable and polished as your craft. Our platform combines high-fashion PDF invoice typography with automated Stripe sync and effortless multi-currency accounting.',
                    'story' => 'Founded in 2024 by engineers and designers tired of rigid legacy enterprise invoicing software, InvoiceHub began as an internal tool. Today, thousands of businesses rely on us to bill millions of dollars each month across 30+ currencies.',
                    'stats' => [
                        ['label' => 'Total Invoiced Volume', 'value' => '$14.8M+'],
                        ['label' => 'Global Customers', 'value' => '24,000+'],
                        ['label' => 'Payment Success Rate', 'value' => '99.4%'],
                        ['label' => 'Supported Currencies', 'value' => '32+'],
                    ],
                    'values' => [
                        ['title' => 'Design as a Differentiator', 'description' => 'An invoice is often the last impression your client gets. It should radiate prestige and trust.'],
                        ['title' => 'Speed & Frictionless Flow', 'description' => 'Generate, preview, customize, and dispatch invoices in under 45 seconds.'],
                        ['title' => 'Ironclad Security', 'description' => 'End-to-end encryption, strict role-based access, automated Stripe webhooks, and secure cloud storage.'],
                        ['title' => 'Customer Centricity', 'description' => 'Every single feature we release is shaped by real feedback from freelancers, studios, and agencies.'],
                    ],
                ],
                'is_published' => true,
            ]
        );

        CmsPage::updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Get in Touch with Our Global Team',
                'subtitle' => 'Have questions regarding our subscription plans, custom invoice templates, or enterprise workflows? We are here 24/7.',
                'meta_description' => 'Contact InvoiceHub support, sales, or enterprise advisory team. We respond within 2 hours.',
                'content' => [
                    'office_locations' => [
                        ['city' => 'San Francisco (HQ)', 'address' => '100 Market St, Suite 400', 'state' => 'CA 94105, USA', 'email' => 'sf@invoicehub.test'],
                        ['city' => 'London', 'address' => '30 St Mary Axe, Floor 14', 'state' => 'EC3A 8EP, UK', 'email' => 'london@invoicehub.test'],
                        ['city' => 'Singapore', 'address' => '10 Collyer Quay, Ocean Financial Centre', 'state' => '049315, Singapore', 'email' => 'sg@invoicehub.test'],
                    ],
                ],
                'is_published' => true,
            ]
        );

        CmsPage::updateOrCreate(
            ['slug' => 'faq'],
            [
                'title' => 'Frequently Asked Questions',
                'subtitle' => 'Everything you need to know about InvoiceHub subscriptions, Stripe checkouts, invoice customization, and credits.',
                'meta_description' => 'Answers to common questions about InvoiceHub plans, billing, PDF generation, and Stripe integrations.',
                'content' => [],
                'is_published' => true,
            ]
        );

        // 3. Frequently Asked Questions
        $faqs = [
            [
                'category' => 'general',
                'question' => 'What makes InvoiceHub different from traditional invoicing software?',
                'answer' => 'InvoiceHub focuses on executive presentation and sheer speed. Unlike bloated ERP software, we provide 4 designer-crafted invoice layout styles (Minimalist, Corporate, Modern Tech, and Elegant Serif) with live real-time preview, instant PDF rendering, and integrated Stripe payment links.',
                'order' => 1,
            ],
            [
                'category' => 'general',
                'question' => 'Can I switch between the 4 invoice styles for different clients?',
                'answer' => 'Absolutely! Each invoice can have its own independent template style. You can issue a Minimalist invoice for tech clients and an Elegant Serif invoice for high-end boutique or law firm clients with a single click.',
                'order' => 2,
            ],
            [
                'category' => 'billing',
                'question' => 'How do invoice credits and subscription packages work?',
                'answer' => 'Every new user receives 5 complimentary starter invoice credits. When you upgrade to Starter or Pro, you receive monthly credit allocations. If you choose our Enterprise plan, you unlock completely unlimited invoice generation with zero credit deductions.',
                'order' => 3,
            ],
            [
                'category' => 'billing',
                'question' => 'How are Stripe payments handled on InvoiceHub?',
                'answer' => 'We integrate directly with Stripe Checkout and Stripe Webhooks. Upgrades are processed securely with SSL encryption, and your plan credits update instantly upon transaction confirmation.',
                'order' => 4,
            ],
            [
                'category' => 'features',
                'question' => 'Can I customize my company logo, default currency, and payment instructions?',
                'answer' => 'Yes! Navigate to Account & Settings (/profile) where you can upload your studio logo or avatar, set your default currency (USD, EUR, GBP, PKR, etc.), and save preset bank wire remittance instructions that auto-fill into every new invoice.',
                'order' => 5,
            ],
            [
                'category' => 'features',
                'question' => 'Does InvoiceHub support PDF export and printing?',
                'answer' => 'Yes! Every invoice can be downloaded instantly as a vector-crisp PDF document or printed directly from your browser. The styling is pixel-perfect and optimized for standard A4 and Letter page sizes.',
                'order' => 6,
            ],
            [
                'category' => 'security',
                'question' => 'How safe is my financial and client data?',
                'answer' => 'We enforce bank-grade security standards including Argon2id/Bcrypt password hashing, CSRF token validation, role-based access middleware, and encrypted database connections. We never store raw credit card numbers on our servers.',
                'order' => 7,
            ],
            [
                'category' => 'security',
                'question' => 'Can I sign in using Google or Firebase OAuth?',
                'answer' => 'Yes! InvoiceHub includes native Firebase Google Authentication. You can sign in with your Google Workspace or Gmail account in a single click without having to remember separate passwords.',
                'order' => 8,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        Setting::purgeCache();
    }
}
