<?php

use App\Http\Controllers\Api\AgenceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\EmployeController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\FidelisationController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\PointageController;
use App\Http\Controllers\Api\TypeArticleController;
use App\Http\Controllers\Api\TypeServiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MissionLivraisonController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AuditLogController;

use Illuminate\Support\Facades\Route;

// Routes publiques auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes publiques sans auth
Route::get('agences', [AgenceController::class, 'index']);
Route::get('agences/{agence}', [AgenceController::class, 'show']);
Route::get('type-articles', [TypeArticleController::class, 'index']);
Route::get('type-services', [TypeServiceController::class, 'index']);
Route::post('clients', [ClientController::class, 'store']); // inscription client
// Suivi commande public
Route::get('commandes/suivi/{numero}', [CommandeController::class, 'suivi']);
// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Agences (modification protégée)
    Route::post('agences', [AgenceController::class, 'store']);
    Route::put('agences/{agence}', [AgenceController::class, 'update']);
    Route::delete('agences/{agence}', [AgenceController::class, 'destroy']);

    // Clients (lecture et modification protégées)
    Route::get('clients', [ClientController::class, 'index']);
    Route::get('clients/{client}', [ClientController::class, 'show']);
    Route::put('clients/{client}', [ClientController::class, 'update']);
    Route::delete('clients/{client}', [ClientController::class, 'destroy']);

    // Commandes
    Route::apiResource('commandes', CommandeController::class);
    Route::patch('commandes/{id}/statut', [CommandeController::class, 'changerStatut']);

    // Catalogue
    Route::post('type-articles', [TypeArticleController::class, 'store']);
    Route::put('type-articles/{type_article}', [TypeArticleController::class, 'update']);
    Route::delete('type-articles/{type_article}', [TypeArticleController::class, 'destroy']);
    Route::post('type-services', [TypeServiceController::class, 'store']);
    Route::put('type-services/{type_service}', [TypeServiceController::class, 'update']);
    Route::delete('type-services/{type_service}', [TypeServiceController::class, 'destroy']);

    // Employés
    Route::apiResource('employes', EmployeController::class);

    // Pointages
    Route::apiResource('pointages', PointageController::class);

    // Facturation
    Route::apiResource('factures', FactureController::class);
    Route::apiResource('paiements', PaiementController::class);

    // Fidélisation
    Route::get('clients/{id}/fidelite', [FidelisationController::class, 'points']);
    Route::post('fidelite/crediter', [FidelisationController::class, 'crediterPoints']);
    Route::post('fidelite/debiter', [FidelisationController::class, 'debiterPoints']);
    Route::post('fidelite/parrainage', [FidelisationController::class, 'parrainage']);
    Route::get('clients/{id}/parrainage', [FidelisationController::class, 'genererCodeParrainage']);

    // Dashboard
    Route::get('dashboard/global', [DashboardController::class, 'global']);
    Route::get('dashboard/agence/{id}', [DashboardController::class, 'agence']);

    // Livraisons
    Route::apiResource('missions', MissionLivraisonController::class);
    Route::patch('missions/{id}/statut', [MissionLivraisonController::class, 'changerStatut']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/non-lues', [NotificationController::class, 'nonLues']);
    Route::patch('notifications/{id}/lu', [NotificationController::class, 'marquerLu']);
    Route::patch('notifications/tout-lu', [NotificationController::class, 'marquerToutLu']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // Dans le groupe auth:sanctum
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    // Dans le groupe auth:sanctum
    Route::get('factures/{id}/pdf', [FactureController::class, 'pdf']);
});