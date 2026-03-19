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
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes publiques sans auth
Route::get('agences', [AgenceController::class, 'index']);
Route::get('agences/{agence}', [AgenceController::class, 'show']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Clients
    Route::apiResource('clients', ClientController::class);
    
    // Commandes
    Route::apiResource('commandes', CommandeController::class);
    Route::patch('commandes/{id}/statut', [CommandeController::class, 'changerStatut']);

    // Catalogue
    Route::apiResource('type-articles', TypeArticleController::class);
    Route::apiResource('type-services', TypeServiceController::class);


    // Employés
    Route::apiResource('employes', EmployeController::class);

    // Pointages
    Route::apiResource('pointages', PointageController::class);


    // Facturation
    Route::apiResource('factures', FactureController::class);
    Route::apiResource('paiements', PaiementController::class);

    // Fidelisation
    Route::get('clients/{id}/fidelite', [FidelisationController::class, 'points']);
    Route::post('fidelite/crediter', [FidelisationController::class, 'crediterPoints']);
    Route::post('fidelite/debiter', [FidelisationController::class, 'debiterPoints']);
    Route::post('fidelite/parrainage', [FidelisationController::class, 'parrainage']);
    Route::get('clients/{id}/parrainage', [FidelisationController::class, 'genererCodeParrainage']);

        // Dashboard
    Route::get('dashboard/global', [DashboardController::class, 'global']);
    Route::get('dashboard/agence/{id}', [DashboardController::class, 'agence']);


    Route::apiResource('missions', MissionLivraisonController::class);
    Route::patch('missions/{id}/statut', [MissionLivraisonController::class, 'changerStatut']);



    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/non-lues', [NotificationController::class, 'nonLues']);
    Route::patch('notifications/{id}/lu', [NotificationController::class, 'marquerLu']);
    Route::patch('notifications/tout-lu', [NotificationController::class, 'marquerToutLu']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
});