<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'COMMANDE_CREEE',
            'COMMANDE_EN_ATTENTE',
            'COMMANDE_COLLECTEE',
            'COMMANDE_RECUE_AGENCE',
            'COMMANDE_EN_TRAITEMENT',
            'COMMANDE_PRETE',
            'COMMANDE_EN_LIVRAISON',
            'COMMANDE_LIVREE',
            'COMMANDE_RETIREE_AGENCE',
            'COMMANDE_ANNULEE',
            'LIVREUR_EN_ROUTE',
            'RAPPEL_RETRAIT',
            'NOUVEAU_NIVEAU_FIDELITE',
            'ALERTE_INTERNE',
            'RAPPORT_JOURNALIER'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'COMMANDE_CREEE',
            'COMMANDE_PRETE',
            'COMMANDE_LIVREE',
            'LIVREUR_EN_ROUTE',
            'RAPPEL_RETRAIT',
            'NOUVEAU_NIVEAU_FIDELITE',
            'ALERTE_INTERNE',
            'RAPPORT_JOURNALIER'
        ) NOT NULL");
    }
};