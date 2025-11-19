<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\MaterialManagementController;
use App\Http\Controllers\Admin\ProfessionalVerificationController;
use App\Http\Controllers\Admin\TemplateManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');

    // Projects
    Route::resource('projects', ProjectController::class)->except(['edit', 'update']);
    Route::post('/projects/{project}/regenerate', [ProjectController::class, 'regenerate'])->name('projects.regenerate');

    // File Downloads
    Route::get('/projects/{project}/download', [FileDownloadController::class, 'download'])->name('projects.download');
    Route::get('/projects/{project}/download-all', [FileDownloadController::class, 'downloadAll'])->name('projects.downloadAll');

    // Professionals
    Route::get('/professionals', [ProfessionalController::class, 'index'])->name('professionals.index');
    Route::get('/professionals/create', [ProfessionalController::class, 'create'])->name('professionals.create');
    Route::post('/professionals', [ProfessionalController::class, 'store'])->name('professionals.store');
    Route::get('/professionals/{professional}', [ProfessionalController::class, 'show'])->name('professionals.show');
    Route::get('/professionals/{professional}/edit', [ProfessionalController::class, 'edit'])->name('professionals.edit');
    Route::put('/professionals/{professional}', [ProfessionalController::class, 'update'])->name('professionals.update');

    // Offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/create', [OfferController::class, 'create'])->name('offers.create');
    Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
    Route::post('/offers/{offer}/accept', [OfferController::class, 'accept'])->name('offers.accept');
    Route::post('/offers/{offer}/reject', [OfferController::class, 'reject'])->name('offers.reject');
    Route::post('/projects/{project}/request-quotes', [OfferController::class, 'requestQuotes'])->name('offers.requestQuotes');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/stats', [OrderController::class, 'stats'])->name('orders.stats');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/update-role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::post('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Professional Verification
    Route::get('/professionals', [ProfessionalVerificationController::class, 'index'])->name('professionals.index');
    Route::get('/professionals/{professional}', [ProfessionalVerificationController::class, 'show'])->name('professionals.show');
    Route::post('/professionals/{professional}/verify', [ProfessionalVerificationController::class, 'verify'])->name('professionals.verify');
    Route::post('/professionals/{professional}/unverify', [ProfessionalVerificationController::class, 'unverify'])->name('professionals.unverify');
    Route::post('/professionals/{professional}/toggle-active', [ProfessionalVerificationController::class, 'toggleActive'])->name('professionals.toggle-active');
    Route::post('/professionals/{professional}/update-notes', [ProfessionalVerificationController::class, 'updateNotes'])->name('professionals.update-notes');

    // Template Management
    Route::get('/templates', [TemplateManagementController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [TemplateManagementController::class, 'create'])->name('templates.create');
    Route::post('/templates', [TemplateManagementController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}/edit', [TemplateManagementController::class, 'edit'])->name('templates.edit');
    Route::put('/templates/{template}', [TemplateManagementController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}', [TemplateManagementController::class, 'destroy'])->name('templates.destroy');
    Route::post('/templates/{template}/toggle-active', [TemplateManagementController::class, 'toggleActive'])->name('templates.toggle-active');

    // Material Management
    Route::get('/materials', [MaterialManagementController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [MaterialManagementController::class, 'create'])->name('materials.create');
    Route::post('/materials', [MaterialManagementController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/edit', [MaterialManagementController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [MaterialManagementController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialManagementController::class, 'destroy'])->name('materials.destroy');
    Route::post('/materials/{material}/toggle-active', [MaterialManagementController::class, 'toggleActive'])->name('materials.toggle-active');
});
