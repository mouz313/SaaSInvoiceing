<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Package;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\CmsAndSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseDTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsAndSettingsSeeder::class);

        $this->user = User::create([
            'name' => 'Agency Founder',
            'email' => 'founder@agency.test',
            'password' => Hash::make('secret123'),
            'role' => 'user',
            'invoice_credits' => 10,
        ]);

        $this->client = Client::create([
            'user_id' => $this->user->id,
            'name' => 'Tech Corp',
            'email' => 'tech@corp.test',
            'company_name' => 'Tech Corp',
            'currency' => 'USD',
        ]);
    }

    public function test_user_can_log_time_entry_and_total_is_calculated(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('time.store'), [
            'client_id' => $this->client->id,
            'project_name' => 'Mobile App',
            'task_description' => 'API integration and endpoint security',
            'hours' => 5.5,
            'hourly_rate' => 100.00,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('time.index'));
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'hours' => 5.50,
            'hourly_rate' => 100.00,
            'total_amount' => 550.00,
            'is_billed' => false,
        ]);
    }

    public function test_user_can_log_billable_expense(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('expenses.store'), [
            'client_id' => $this->client->id,
            'category' => 'Hosting',
            'description' => 'Dedicated AWS server instance',
            'amount' => 249.50,
            'currency' => 'USD',
            'expense_date' => Carbon::today()->format('Y-m-d'),
            'is_billable' => 1,
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'category' => 'Hosting',
            'amount' => 249.50,
            'is_billable' => true,
            'is_billed' => false,
        ]);
    }

    public function test_client_unbilled_items_endpoint_returns_unbilled_records(): void
    {
        $this->actingAs($this->user);

        TimeEntry::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'task_description' => 'Design wireframes',
            'hours' => 3,
            'hourly_rate' => 80,
            'date' => Carbon::today(),
        ]);

        Expense::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'category' => 'Software',
            'description' => 'Plugin license',
            'amount' => 79.00,
            'expense_date' => Carbon::today(),
            'is_billable' => true,
        ]);

        $response = $this->get(route('clients.unbilled-items', $this->client));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'time_entries');
        $response->assertJsonCount(1, 'expenses');
        $this->assertEquals('Design wireframes', $response->json('time_entries.0.task_description'));
        $this->assertEquals(79.00, $response->json('expenses.0.amount'));
    }

    public function test_creating_invoice_with_imported_items_marks_them_as_billed(): void
    {
        $this->actingAs($this->user);

        $timeEntry = TimeEntry::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'task_description' => 'Frontend coding',
            'hours' => 4,
            'hourly_rate' => 90,
            'date' => Carbon::today(),
        ]);

        $expense = Expense::create([
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'category' => 'Travel',
            'description' => 'Client on-site visit',
            'amount' => 120.00,
            'expense_date' => Carbon::today(),
            'is_billable' => true,
        ]);

        $response = $this->post(route('invoices.store'), [
            'client_id' => $this->client->id,
            'invoice_number' => 'INV-BILL-001',
            'invoice_date' => Carbon::today()->format('Y-m-d'),
            'due_date' => Carbon::today()->addDays(14)->format('Y-m-d'),
            'status' => 'sent',
            'style' => 'minimalist',
            'currency' => 'USD',
            'items' => [
                ['description' => 'Time: Frontend coding', 'quantity' => 4, 'unit_price' => 90],
                ['description' => 'Expense: Travel', 'quantity' => 1, 'unit_price' => 120],
            ],
            'time_entry_ids' => [$timeEntry->id],
            'expense_ids' => [$expense->id],
        ]);

        $response->assertRedirect(route('invoices.index'));

        $this->assertTrue($timeEntry->fresh()->is_billed);
        $this->assertNotNull($timeEntry->fresh()->invoice_id);

        $this->assertTrue($expense->fresh()->is_billed);
        $this->assertNotNull($expense->fresh()->invoice_id);
    }

    public function test_free_tier_limits_client_creation_to_five_clients(): void
    {
        $this->actingAs($this->user);

        // User already has 1 client ($this->client). Create 4 more to hit limit of 5.
        for ($i = 2; $i <= 5; $i++) {
            $this->user->clients()->create([
                'name' => "Client {$i}",
                'country' => 'US',
            ]);
        }

        $this->assertEquals(5, $this->user->clients()->count());
        $this->assertEquals(5, $this->user->clientLimit());
        $this->assertFalse($this->user->canCreateClient());

        // Attempting to visit create or post store should be blocked
        $response = $this->get(route('clients.create'));
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('error');

        $storeResponse = $this->post(route('clients.store'), [
            'name' => '6th Client Attempt',
            'country' => 'US',
        ]);
        $storeResponse->assertRedirect(route('clients.index'));
        $this->assertEquals(5, $this->user->clients()->count());
    }

    public function test_paid_package_unlocks_unlimited_clients(): void
    {
        $proPackage = Package::create([
            'name' => 'Pro Unlimited',
            'slug' => 'pro-unlimited',
            'price' => 29.00,
            'billing_period' => 'monthly',
            'invoice_limit' => -1,
        ]);

        $this->user->update(['package_id' => $proPackage->id]);

        $this->assertEquals(-1, $this->user->clientLimit());
        $this->assertTrue($this->user->canCreateClient());
        $this->assertTrue($this->user->hasFeature('recurring_invoices'));
    }
}
