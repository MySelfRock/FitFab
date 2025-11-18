<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - FitFab
|--------------------------------------------------------------------------
|
| REST API for FitFab - Furniture Project Generator SaaS
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Templates (public)
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{template}', [TemplateController::class, 'show']);

    // Materials (public)
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/{material}', [MaterialController::class, 'show']);

    // Professionals search (public)
    Route::get('/professionals', [ProfessionalController::class, 'index']);
    Route::get('/professionals/{professional}', [ProfessionalController::class, 'show']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Projects
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/stats', [ProjectController::class, 'stats']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::post('/projects/{project}/regenerate', [ProjectController::class, 'regenerate']);
        Route::get('/projects/{project}/download', [ProjectController::class, 'download']);
        Route::post('/projects/{project}/request-quote', [ProjectController::class, 'requestQuote']);

        // Professional Profile
        Route::post('/professional', [ProfessionalController::class, 'store']);
        Route::put('/professionals/{professional}', [ProfessionalController::class, 'update']);

        // Offers
        Route::get('/offers', [OfferController::class, 'index']);
        Route::post('/offers', [OfferController::class, 'store']);
        Route::get('/offers/{offer}', [OfferController::class, 'show']);
        Route::put('/offers/{offer}', [OfferController::class, 'update']);
        Route::post('/offers/{offer}/accept', [OfferController::class, 'accept']);
        Route::post('/offers/{offer}/reject', [OfferController::class, 'reject']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/pay', [OrderController::class, 'pay']);
        Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });
});
