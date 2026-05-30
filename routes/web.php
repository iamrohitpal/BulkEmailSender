<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SmtpSettingController as AdminSmtpSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\CampaignController as UserCampaignController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\SmtpSettingController as UserSmtpSettingController;
use Illuminate\Support\Facades\Route;

// Root redirects to user login
Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// USER (Tenant) Authentication & SaaS Routes
// ==========================================
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserAuthController::class, 'login']);
    
    Route::get('/register', [UserAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [UserAuthController::class, 'register']);
    
    Route::get('/forgot-password', [UserAuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [UserAuthController::class, 'forgot'])->name('password.email');
});

Route::middleware(['auth:web', 'user.active'])->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

    // SMTP Settings CRUD
    Route::resource('smtp', UserSmtpSettingController::class)->names([
        'index' => 'user.smtp.index',
        'create' => 'user.smtp.create',
        'store' => 'user.smtp.store',
        'edit' => 'user.smtp.edit',
        'update' => 'user.smtp.update',
        'destroy' => 'user.smtp.destroy',
    ])->except(['show']);
    Route::post('/smtp/{smtp}/test', [UserSmtpSettingController::class, 'sendTestEmail'])->name('user.smtp.test');

    // Campaign Column Mapping
    Route::get('/campaigns/map', [UserCampaignController::class, 'showMap'])->name('user.campaigns.map');
    Route::post('/campaigns/map', [UserCampaignController::class, 'storeMap'])->name('user.campaigns.map.store');

    // Campaigns CRUD & Actions
    Route::resource('campaigns', UserCampaignController::class)->names([
        'index' => 'user.campaigns.index',
        'create' => 'user.campaigns.create',
        'store' => 'user.campaigns.store',
        'show' => 'user.campaigns.show',
        'edit' => 'user.campaigns.edit',
        'update' => 'user.campaigns.update',
        'destroy' => 'user.campaigns.destroy',
    ]);
    Route::post('/campaigns/{campaign}/send', [UserCampaignController::class, 'send'])->name('user.campaigns.send');
    Route::post('/campaigns/{campaign}/pause', [UserCampaignController::class, 'pause'])->name('user.campaigns.pause');
    Route::post('/campaigns/{campaign}/stop', [UserCampaignController::class, 'stop'])->name('user.campaigns.stop');
    Route::get('/campaigns/{campaign}/progress', [UserCampaignController::class, 'getProgress'])->name('user.campaigns.progress');
});

// ==========================================
// SUPER ADMIN Authentication & SaaS Panel
// ==========================================
Route::prefix('admin')->group(function () {
    // Guest Admin Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    // Authenticated Admin Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // SMTP Settings CRUD (Super Admin)
        Route::resource('smtp', AdminSmtpSettingController::class)->names([
            'index' => 'admin.smtp.index',
            'create' => 'admin.smtp.create',
            'store' => 'admin.smtp.store',
            'edit' => 'admin.smtp.edit',
            'update' => 'admin.smtp.update',
            'destroy' => 'admin.smtp.destroy',
        ])->except(['show']);
        Route::post('/smtp/{smtp}/test', [AdminSmtpSettingController::class, 'sendTestEmail'])->name('admin.smtp.test');

        // Campaigns CRUD & Actions (Super Admin)
        Route::resource('campaigns', AdminCampaignController::class)->names([
            'index' => 'admin.campaigns.index',
            'create' => 'admin.campaigns.create',
            'store' => 'admin.campaigns.store',
            'show' => 'admin.campaigns.show',
            'edit' => 'admin.campaigns.edit',
            'update' => 'admin.campaigns.update',
            'destroy' => 'admin.campaigns.destroy',
        ]);
        Route::post('/campaigns/{campaign}/send', [AdminCampaignController::class, 'send'])->name('admin.campaigns.send');
        Route::get('/campaigns/{campaign}/progress', [AdminCampaignController::class, 'getProgress'])->name('admin.campaigns.progress');

        // User Accounts Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('admin.users.toggle');
    });
});