<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // 👈 Très important pour le typage de la fonction

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // On intercepte les invités (non connectés) pour éviter la redirection web
        $middleware->redirectGuestsTo(function (Request $request) {
            // Si l'URL commence par api/, on renvoie du JSON au lieu de chercher une route 'login'
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            
            return '/login'; // Fallback pour les requêtes web classiques
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();