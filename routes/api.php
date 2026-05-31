
<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Utilisateur
    Route::get('/users/me', [AuthController::class, 'me']);
    Route::put('/users/me', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Transactions (génère automatiquement index, store, show, update, destroy)
    Route::apiResource('transactions', TransactionController::class);

    // Objectifs
    Route::apiResource('goals', GoalController::class);
    // Route spécifique pour PATCH (bien que update de apiResource puisse suffire, tu peux être explicite)
    Route::patch('/goals/{goal}/progress', [GoalController::class, 'updateProgress']);

    // Analyses
    Route::get('/analytics/kpis', [AnalyticsController::class, 'kpis']);
    Route::get('/analytics/charts', [AnalyticsController::class, 'charts']);
});
