<?php

namespace Tests\Feature;

use App\Mail\ClientStatementMail;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use App\Services\StatementService;
use App\Support\Currency;
use Carbon\Carbon;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PhaseCTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);

        $this->user = User::create([
            'name' => 'John Enterprise',
            'email' => 'john@enterprise.test',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Global Corp',
            'email' => 'finance@acmeglobal.test',
            'phone' => '+15550001111',
            'company_name' => 'Acme Global',
            'currency' => 'USD',
        ]);
    }

    public function test_client_automatically_generates_portal_access_token_and_default_currency(): void
    {
        $this->assertNotEmpty($this->client->portal_access_token);
        $this->assertEquals(60, strlen($this->client->portal_access_token));
        $this->assertEquals('USD', $this->client->currency);
        $this->assertEquals('$', $this->client->currency_symbol);
        $this->assertStringContainsString($this->client->portal_access_token, $this->client->portal_url);
    }

    public function test_client_can_access_portal_via_magic_token(): void
    {
        $response = $this->get(route('portal.access', $this->client->portal_access_token));

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertEquals($this->client->id, session('portal_client_id'));
    }

    public function test_unauthenticated_client_is_redirected_to_portal_login(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('portal.login'));
    }

    public function test_client_portal_dashboard_loads_with_invoices_and_balances(): void
    {
        // Create an invoice for this client
        $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-PORTAL-01',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'subtotal' => 850.00,
            'total' => 850.00,
            'balance_due' => 850.00,
        ]);

        $response = $this->withSession(['portal_client_id' => $this->client->id])
            ->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Acme Global Corp');
        $response->assertSee('INV-PORTAL-01');
        $response->assertSee('850.00');
    }

    public function test_client_portal_invoices_page_filters(): void
    {
        $inv1 = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-UNPAID-1',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'total' => 500.00,
            'balance_due' => 500.00,
        ]);

        $inv2 = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-PAID-1',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'paid',
            'style' => 'minimalist',
            'currency' => 'USD',
            'total' => 300.00,
            'amount_paid' => 300.00,
            'balance_due' => 0.00,
        ]);

        $response = $this->withSession(['portal_client_id' => $this->client->id])
            ->get(route('portal.invoices', ['status' => 'unpaid']));

        $response->assertStatus(200);
        $response->assertSee('INV-UNPAID-1');
        $response->assertDontSee('INV-PAID-1');

        $responsePaid = $this->withSession(['portal_client_id' => $this->client->id])
            ->get(route('portal.invoices', ['status' => 'paid']));

        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('INV-PAID-1');
        $responsePaid->assertDontSee('INV-UNPAID-1');
    }

    public function test_client_can_accept_and_decline_estimates_in_portal(): void
    {
        $estimate = Estimate::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-PORTAL-01',
            'estimate_date' => Carbon::today(),
            'expiry_date' => Carbon::today()->addDays(30),
            'status' => 'sent',
            'currency' => 'USD',
            'total' => 1200.00,
        ]);

        $response = $this->withSession(['portal_client_id' => $this->client->id])
            ->post(route('portal.estimates.accept', $estimate));

        $response->assertSessionHas('success');
        $this->assertEquals('accepted', $estimate->fresh()->status);

        $estimate2 = Estimate::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'estimate_number' => 'EST-PORTAL-02',
            'estimate_date' => Carbon::today(),
            'expiry_date' => Carbon::today()->addDays(30),
            'status' => 'sent',
            'currency' => 'USD',
            'total' => 900.00,
        ]);

        $response2 = $this->withSession(['portal_client_id' => $this->client->id])
            ->post(route('portal.estimates.decline', $estimate2));

        $response2->assertSessionHas('info');
        $this->assertEquals('declined', $estimate2->fresh()->status);
    }

    public function test_statement_service_calculates_opening_balance_and_running_ledger(): void
    {
        $startDate = Carbon::today()->subDays(15);
        $endDate = Carbon::today();

        // 1. Prior invoice before start date: $1000
        $priorInvoice = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-PRIOR-01',
            'invoice_date' => Carbon::today()->subDays(30),
            'due_date' => Carbon::today()->subDays(16),
            'status' => 'partially_paid',
            'style' => 'minimalist',
            'currency' => 'USD',
            'total' => 1000.00,
            'balance_due' => 1000.00,
        ]);
        // Prior payment: $400
        $priorInvoice->recordPayment(400.00, 'bank_transfer', 'REF-PRIOR', 'Prior deposit', Carbon::today()->subDays(25));
        // Prior net balance owed = 1000 - 400 = 600

        // 2. Invoice in current period: $700
        $currentInvoice = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-CURRENT-01',
            'invoice_date' => Carbon::today()->subDays(10),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'total' => 700.00,
            'balance_due' => 700.00,
        ]);

        // 3. Payment in current period: $300
        $currentInvoice->recordPayment(300.00, 'stripe', 'pi_test_current', 'Payment', Carbon::today()->subDays(5));

        $service = new StatementService;
        $statement = $service->generate($this->client, $startDate, $endDate);

        $this->assertEquals(600.00, $statement['openingBalance']);
        $this->assertEquals(700.00, $statement['totalInvoiced']);
        $this->assertEquals(300.00, $statement['totalPaid']);
        // Closing balance = 600 (opening) + 700 (invoiced) - 300 (paid) = 1000.00
        $this->assertEquals(1000.00, $statement['closingBalance']);
        $this->assertCount(2, $statement['ledger']); // 1 invoice + 1 payment in period
    }

    public function test_business_owner_can_view_statement_and_download_pdf(): void
    {
        $this->actingAs($this->user);

        // View Statement Web Page
        $response = $this->get(route('clients.statement', ['client' => $this->client, 'range' => 'year_to_date']));
        $response->assertStatus(200);
        $response->assertSee('Statement of Account');
        $response->assertSee('Acme Global Corp');

        // Download Statement PDF
        $pdfResponse = $this->get(route('clients.statement.pdf', ['client' => $this->client, 'range' => 'year_to_date']));
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_business_owner_can_email_statement_to_client(): void
    {
        Mail::fake();
        $this->actingAs($this->user);

        $response = $this->post(route('clients.statement.email', $this->client), [
            'range' => 'all_time',
            'custom_message' => 'Here is your summary of accounts.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(ClientStatementMail::class, function ($mail) {
            return $mail->hasTo($this->client->email) &&
                   $mail->customMessage === 'Here is your summary of accounts.' &&
                   $mail->client->id === $this->client->id;
        });
    }

    public function test_client_portal_statement_view_and_pdf_download(): void
    {
        $response = $this->withSession(['portal_client_id' => $this->client->id])
            ->get(route('portal.statement', ['range' => 'all_time']));

        $response->assertStatus(200);
        $response->assertSee('Statement of Account');

        $pdfResponse = $this->withSession(['portal_client_id' => $this->client->id])
            ->get(route('portal.statement.pdf', ['range' => 'all_time']));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_multi_currency_support_and_formatting(): void
    {
        $this->assertEquals('$', Currency::symbol('USD'));
        $this->assertEquals('€', Currency::symbol('EUR'));
        $this->assertEquals('£', Currency::symbol('GBP'));
        $this->assertEquals('Rs', Currency::symbol('PKR'));
        $this->assertEquals('AED', Currency::symbol('AED'));

        $this->assertEquals('$ 1,250.00', Currency::format(1250, 'USD'));
        $this->assertEquals('€ 950.50', Currency::format(950.5, 'EUR'));
        $this->assertEquals('Rs 45,000.00', Currency::format(45000, 'PKR'));

        // Test model accessors
        $invoice = $this->user->invoices()->create([
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-CURR-01',
            'invoice_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'EUR',
            'total' => 250.00,
            'balance_due' => 250.00,
        ]);

        $this->assertEquals('€', $invoice->currency_symbol);
        $this->assertEquals('€ 250.00', $invoice->formatted_total);
        $this->assertEquals('€ 250.00', $invoice->formatted_balance_due);
    }
}
