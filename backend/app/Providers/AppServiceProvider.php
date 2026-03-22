<?php

namespace App\Providers;

use App\Models\Agence;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Facture;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
     /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Agence::observe(AuditObserver::class);
        Client::observe(AuditObserver::class);
        Commande::observe(AuditObserver::class);
        Employe::observe(AuditObserver::class);
        Facture::observe(AuditObserver::class);
    }
}