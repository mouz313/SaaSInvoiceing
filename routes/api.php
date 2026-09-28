<?php

use App\Http\Controllers\Api\V1\ClientApiController;
use App\Http\Controllers\Api\V1\InvoiceApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public REST API V1 Routes
|--------------------------------------------------------------------------
|
| Authenticated via Bearer token (ih_live_...) with abilities.
|
*/

Route::prefix('v1')->middleware(['auth.api_key', 'throttle:api-keys'])->group(function () {
    // Invoices API
    Route::get('/invoices', [InvoiceApiController::class, 'index'])->name('api.v1.invoices.index')->middleware('auth.api_key:invoices:read');
    Route::post('/invoices', [InvoiceApiController::class, 'store'])->name('api.v1.invoices.store')->middleware('auth.api_key:invoices:write');
    Route::get('/invoices/{invoice}', [InvoiceApiController::class, 'show'])->name('api.v1.invoices.show')->middleware('auth.api_key:invoices:read');
    Route::post('/invoices/{invoice}/payments', [InvoiceApiController::class, 'recordPayment'])->name('api.v1.invoices.payments')->middleware('auth.api_key:payments:write');

    // Clients API
    Route::get('/clients', [ClientApiController::class, 'index'])->name('api.v1.clients.index')->middleware('auth.api_key:clients:read');
    Route::post('/clients', [ClientApiController::class, 'store'])->name('api.v1.clients.store')->middleware('auth.api_key:clients:write');
});
