<?php

use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\UserLogoController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('pages.contact.submit');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

// Public Client Invoice & Payment Portal
Route::get('/pay/{token}', [PublicInvoiceController::class, 'show'])->name('invoices.public');
Route::get('/pay/{token}/pdf', [PublicInvoiceController::class, 'pdf'])->name('invoices.public.pdf');
Route::post('/pay/{token}/checkout', [PublicInvoiceController::class, 'checkout'])->name('invoices.public.checkout');
Route::get('/pay/{token}/success', [PublicInvoiceController::class, 'success'])->name('invoices.public.success');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('auth.demo-login');
Route::post('/auth/firebase-session', [AuthController::class, 'firebaseSession'])->name('auth.firebase-session');

Route::post('/webhook/stripe', [WebhookController::class, 'handleStripe'])->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Client Management
    Route::resource('clients', ClientController::class);

    // Products & Categories Management
    Route::resource('products', ProductController::class);
    Route::post('/product-categories', [ProductController::class, 'storeCategory'])->name('product-categories.store');
    Route::delete('/product-categories/{category}', [ProductController::class, 'destroyCategory'])->name('product-categories.destroy');

    // Invoicing Engine
    Route::resource('invoices', InvoiceController::class);
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    Route::patch('/invoices/{invoice}/style', [InvoiceController::class, 'updateStyle'])->name('invoices.update-style');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');

    // Logo Gallery
    Route::get('/logos', [UserLogoController::class, 'index'])->name('logos.index');
    Route::post('/logos', [UserLogoController::class, 'store'])->name('logos.store');
    Route::delete('/logos/{logo}', [UserLogoController::class, 'destroy'])->name('logos.destroy');

    // Settings & Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/personal', [ProfileController::class, 'updatePersonal'])->name('profile.personal');
    Route::put('/profile/business', [ProfileController::class, 'updateBusiness'])->name('profile.business');
    Route::put('/profile/invoicing', [ProfileController::class, 'updateInvoicing'])->name('profile.invoicing');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Stripe Billing & Packages
    Route::post('/checkout/{package}', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/checkout/simulate/{package}', [StripeController::class, 'simulate'])->name('stripe.simulate');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard & Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/credits', [AdminController::class, 'updateUserCredits'])->name('users.update-credits');
    Route::post('/users/{user}/toggle-role', [AdminController::class, 'toggleUserRole'])->name('users.toggle-role');
    Route::get('/invoices', [AdminController::class, 'invoices'])->name('invoices');
    Route::get('/packages', [AdminController::class, 'packages'])->name('packages');

    // CMS Management
    Route::get('/cms/pages', [CmsController::class, 'pages'])->name('cms.pages');
    Route::get('/cms/pages/{slug}/edit', [CmsController::class, 'editPage'])->name('cms.pages.edit');
    Route::put('/cms/pages/{slug}', [CmsController::class, 'updatePage'])->name('cms.pages.update');

    Route::get('/cms/faqs', [CmsController::class, 'faqs'])->name('cms.faqs');
    Route::post('/cms/faqs', [CmsController::class, 'storeFaq'])->name('cms.faqs.store');
    Route::put('/cms/faqs/{faq}', [CmsController::class, 'updateFaq'])->name('cms.faqs.update');
    Route::delete('/cms/faqs/{faq}', [CmsController::class, 'deleteFaq'])->name('cms.faqs.delete');

    Route::get('/cms/inquiries', [CmsController::class, 'inquiries'])->name('cms.inquiries');
    Route::patch('/cms/inquiries/{inquiry}/status', [CmsController::class, 'updateInquiryStatus'])->name('cms.inquiries.status');
    Route::delete('/cms/inquiries/{inquiry}', [CmsController::class, 'deleteInquiry'])->name('cms.inquiries.delete');

    // System Settings (Social, Stripe, Firebase, General)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
