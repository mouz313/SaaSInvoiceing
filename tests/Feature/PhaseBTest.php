<?php

namespace Tests\Feature;

use App\Mail\PaymentReceiptMail;
use App\Mail\PaymentReminderMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\RecurringInvoice;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PhaseBTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);

        $this->user = User::create([
            'name' => 'John Automator',
            'email' => 'john@automator.test',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Beta Tech LLC',
            'email' => 'finance@betatech.test',
            'phone' => '+15559876543',
            'company_name' => 'Beta Tech',
        ]);
    }

    private function createSampleInvoice(float $total = 1000.00, ?Carbon $dueDate = null, string $status = 'sent'): Invoice
    {
        $dueDate = $dueDate ?? Carbon::today()->addDays(14);

        $invoice = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => Carbon::today(),
            'due_date' => $dueDate,
            'status' => $status,
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => $total,
            'total' => $total,
            'amount_paid' => 0.00,
            'balance_due' => $total,
        ]);

        $invoice->items()->create([
            'description' => 'Software Consulting',
            'quantity' => 1,
            'unit_price' => $total,
            'amount' => $total,
        ]);

        return $invoice;
    }

    public function test_can_record_partial_payment_and_updates_balance_and_status(): void
    {
        $invoice = $this->createSampleInvoice(1000.00);

        $response = $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount' => 400.00,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'WIRE-9921',
                'paid_at' => Carbon::today()->toDateString(),
                'notes' => '40% initial deposit received',
            ]);

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(400.00, (float) $invoice->amount_paid);
        $this->assertEquals(600.00, (float) $invoice->balance_due);
        $this->assertEquals('partially_paid', $invoice->status);

        $this->assertDatabaseHas('invoice_payments', [
            'invoice_id' => $invoice->id,
            'amount' => 400.00,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'WIRE-9921',
        ]);
    }

    public function test_paying_full_balance_marks_invoice_paid_and_sends_receipt_email(): void
    {
        Mail::fake();

        $invoice = $this->createSampleInvoice(500.00);

        // Record full payment
        $response = $this->actingAs($this->user)
            ->post(route('invoices.payments.store', $invoice), [
                'amount' => 500.00,
                'payment_method' => 'cash',
                'reference_number' => 'RECEIPT-01',
                'paid_at' => Carbon::today()->toDateString(),
                'notes' => 'Settled in cash',
            ]);

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(500.00, (float) $invoice->amount_paid);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        Mail::assertSent(PaymentReceiptMail::class, function ($mail) use ($invoice) {
            return $mail->hasTo($this->client->email) &&
                   $mail->invoice->id === $invoice->id;
        });
    }

    public function test_deleting_payment_recalculates_balance(): void
    {
        $invoice = $this->createSampleInvoice(1000.00);
        $payment = $invoice->recordPayment(300.00, 'cash');

        $invoice->refresh();
        $this->assertEquals(300.00, (float) $invoice->amount_paid);
        $this->assertEquals(700.00, (float) $invoice->balance_due);

        $response = $this->actingAs($this->user)
            ->delete(route('invoices.payments.destroy', [$invoice, $payment]));

        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(0.00, (float) $invoice->amount_paid);
        $this->assertEquals(1000.00, (float) $invoice->balance_due);
        $this->assertEquals('sent', $invoice->status);
        $this->assertDatabaseMissing('invoice_payments', ['id' => $payment->id]);
    }

    public function test_public_invoice_checkout_with_partial_amount(): void
    {
        $invoice = $this->createSampleInvoice(800.00);

        // Client makes a simulated partial payment of $300
        $response = $this->post(route('invoices.public.checkout', $invoice->public_token), [
            'amount' => 300.00,
            'simulate' => true,
        ]);

        $response->assertRedirect(route('invoices.public.success', $invoice->public_token));

        $invoice->refresh();
        $this->assertEquals(300.00, (float) $invoice->amount_paid);
        $this->assertEquals(500.00, (float) $invoice->balance_due);
        $this->assertEquals('partially_paid', $invoice->status);
    }

    public function test_recurring_invoice_crud_operations(): void
    {
        // 1. Index
        $response = $this->actingAs($this->user)->get(route('recurring.index'));
        $response->assertOk();

        // 2. Create Page
        $response = $this->actingAs($this->user)->get(route('recurring.create'));
        $response->assertOk();

        // 3. Store
        $postData = [
            'title' => 'Monthly SEO & Cloud Hosting',
            'client_id' => $this->client->id,
            'frequency' => 'monthly',
            'start_date' => Carbon::today()->toDateString(),
            'due_days' => 14,
            'currency' => 'USD',
            'style' => 'minimalist',
            'auto_send_email' => 1,
            'items' => [
                ['description' => 'Dedicated VPS Hosting', 'quantity' => 1, 'unit_price' => 250.00],
                ['description' => 'SEO Monitoring', 'quantity' => 1, 'unit_price' => 150.00],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('recurring.store'), $postData);
        $response->assertSessionHas('success');

        $profile = RecurringInvoice::where('title', 'Monthly SEO & Cloud Hosting')->first();
        $this->assertNotNull($profile);
        $this->assertEquals(400.00, (float) $profile->total);
        $this->assertEquals('active', $profile->status);
        $this->assertCount(2, $profile->items);

        // 4. Show Page
        $response = $this->actingAs($this->user)->get(route('recurring.show', $profile));
        $response->assertOk();

        // 5. Toggle Status (Pause / Resume)
        $this->actingAs($this->user)->patch(route('recurring.toggle-status', $profile));
        $profile->refresh();
        $this->assertEquals('paused', $profile->status);

        $this->actingAs($this->user)->patch(route('recurring.toggle-status', $profile));
        $profile->refresh();
        $this->assertEquals('active', $profile->status);

        // 6. Delete
        $response = $this->actingAs($this->user)->delete(route('recurring.destroy', $profile));
        $response->assertRedirect(route('recurring.index'));
        $this->assertDatabaseMissing('recurring_invoices', ['id' => $profile->id]);
    }

    public function test_recurring_invoice_generate_now_creates_invoice_and_advances_date(): void
    {
        $profile = $this->user->recurringInvoices()->create([
            'client_id' => $this->client->id,
            'title' => 'Weekly Maintenance',
            'frequency' => 'weekly',
            'start_date' => Carbon::today(),
            'next_issue_date' => Carbon::today(),
            'due_days' => 7,
            'currency' => 'USD',
            'style' => 'minimalist',
            'subtotal' => 200.00,
            'total' => 200.00,
            'auto_send_email' => false,
            'status' => 'active',
        ]);

        $profile->items()->create([
            'description' => 'Weekly Code Review & Security Audit',
            'quantity' => 1,
            'unit_price' => 200.00,
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->user)->post(route('recurring.generate-now', $profile));
        $response->assertSessionHas('success');

        // Verify generated invoice
        $generatedInvoice = Invoice::where('recurring_invoice_id', $profile->id)->first();
        $this->assertNotNull($generatedInvoice);
        $this->assertEquals(200.00, (float) $generatedInvoice->total);
        $this->assertEquals($this->client->id, $generatedInvoice->client_id);
        $this->assertCount(1, $generatedInvoice->items);

        // Verify profile updated
        $profile->refresh();
        $this->assertEquals(1, $profile->invoices_generated_count);
        $this->assertNotNull($profile->last_generated_at);
        $this->assertEquals(Carbon::today()->addWeek()->toDateString(), $profile->next_issue_date->toDateString());
    }

    public function test_process_recurring_invoices_command(): void
    {
        // Profile due today
        $profile = $this->user->recurringInvoices()->create([
            'client_id' => $this->client->id,
            'title' => 'Scheduled Monthly Retainer',
            'frequency' => 'monthly',
            'start_date' => Carbon::today()->subMonth(),
            'next_issue_date' => Carbon::today(),
            'due_days' => 14,
            'currency' => 'USD',
            'style' => 'minimalist',
            'subtotal' => 500.00,
            'total' => 500.00,
            'auto_send_email' => false,
            'status' => 'active',
        ]);

        $profile->items()->create([
            'description' => 'Monthly Retainer Hours',
            'quantity' => 1,
            'unit_price' => 500.00,
            'amount' => 500.00,
        ]);

        // Run the console command
        $this->artisan('invoices:process-recurring')
            ->expectsOutputToContain('Checking for scheduled recurring invoices')
            ->assertExitCode(0);

        $this->assertDatabaseHas('invoices', [
            'recurring_invoice_id' => $profile->id,
            'total' => 500.00,
        ]);

        $profile->refresh();
        $this->assertEquals(1, $profile->invoices_generated_count);
        $this->assertEquals(Carbon::today()->addMonth()->toDateString(), $profile->next_issue_date->toDateString());
    }

    public function test_send_payment_reminders_command_triggers_3_tiers_correctly(): void
    {
        Mail::fake();

        $today = Carbon::today();

        // 1. Tier 1: 3 days before due date
        $invUpcoming = $this->createSampleInvoice(300.00, $today->copy()->addDays(3));

        // 2. Tier 2: Due today
        $invDueToday = $this->createSampleInvoice(400.00, $today->copy());

        // 3. Tier 3: Overdue (5 days past due)
        $invOverdue = $this->createSampleInvoice(500.00, $today->copy()->subDays(5));

        // Run artisan reminder command
        $this->artisan('invoices:send-reminders')
            ->assertExitCode(0);

        // Verify Upcoming Reminder sent
        Mail::assertSent(PaymentReminderMail::class, function ($mail) use ($invUpcoming) {
            return $mail->invoice->id === $invUpcoming->id &&
                   $mail->reminderType === 'upcoming';
        });

        // Verify Due Today Reminder sent
        Mail::assertSent(PaymentReminderMail::class, function ($mail) use ($invDueToday) {
            return $mail->invoice->id === $invDueToday->id &&
                   $mail->reminderType === 'due_today';
        });

        // Verify Overdue Reminder sent and status updated
        Mail::assertSent(PaymentReminderMail::class, function ($mail) use ($invOverdue) {
            return $mail->invoice->id === $invOverdue->id &&
                   $mail->reminderType === 'overdue' &&
                   $mail->daysOverdue === 5;
        });

        $invOverdue->refresh();
        $this->assertEquals('overdue', $invOverdue->status);
        $this->assertEquals(1, $invOverdue->reminder_count);
        $this->assertNotNull($invOverdue->last_reminder_sent_at);
    }
}
