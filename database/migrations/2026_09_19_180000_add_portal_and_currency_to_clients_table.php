<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD')->after('country');
            $table->string('portal_access_token', 64)->nullable()->unique()->after('tax_id');
            $table->string('password')->nullable()->after('portal_access_token');
        });

        foreach (Client::whereNull('portal_access_token')->cursor() as $client) {
            $client->update([
                'portal_access_token' => Str::random(60),
                'currency' => $client->currency ?: 'USD',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['currency', 'portal_access_token', 'password']);
        });
    }
};
