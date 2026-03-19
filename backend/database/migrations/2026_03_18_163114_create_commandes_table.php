<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_commande')->unique();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->uuid('agence_id');
            $table->foreign('agence_id')->references('id')->on('agences')->onDelete('cascade');
            $table->dateTime('date_depot');
            $table->dateTime('date_retrait_prevue')->nullable();
            $table->dateTime('date_retrait_effective')->nullable();
            $table->enum('statut', [
                'CREEE', 'EN_ATTENTE', 'COLLECTEE', 'RECUE_AGENCE',
                'EN_TRAITEMENT', 'PRETE', 'EN_LIVRAISON', 'LIVREE',
                'RETIREE_AGENCE', 'ANNULEE'
            ])->default('CREEE');
            $table->enum('mode_livraison', ['EN_AGENCE', 'LIVRAISON_DOMICILE'])->default('EN_AGENCE');
            $table->string('adresse_livraison')->nullable();
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};