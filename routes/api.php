<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\BillingWebhookController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CategoryGoalController;
use App\Http\Controllers\Api\V1\DebtController;
use App\Http\Controllers\Api\V1\FinancialAccountController;
use App\Http\Controllers\Api\V1\InvestmentController;
use App\Http\Controllers\Api\V1\MonthlyReportController;
use App\Http\Controllers\Api\V1\PlanHistoryController;
use App\Http\Controllers\Api\V1\ProjectionController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\RecurrenceSeriesController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('billing/plans', [BillingController::class, 'plans']);
    Route::post('billing/webhook', BillingWebhookController::class);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::post('billing/checkout', [BillingController::class, 'checkout']);

        Route::get('projection', [ProjectionController::class, 'show']);
        Route::get('recommendations', [RecommendationController::class, 'index'])->middleware('feature:recommendations');
        Route::get('reports/monthly', MonthlyReportController::class)->middleware('feature:export_pdf');

        Route::apiResource('financial-accounts', FinancialAccountController::class);
        Route::apiResource('category-goals', CategoryGoalController::class)->except(['show']);
        Route::get('plan-histories', [PlanHistoryController::class, 'index']);
        Route::post('plan-histories', [PlanHistoryController::class, 'store']);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('recurrence-series', RecurrenceSeriesController::class);
        Route::apiResource('debts', DebtController::class);
        Route::apiResource('investments', InvestmentController::class)->middleware('feature:investments');
    });
});
