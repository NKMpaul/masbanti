<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_facture')->unique();
            $table->uuid('commande_id');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->uuid('agence_id');
            $table->foreign('agence_id')->references('id')->on('agences')->onDelete('cascade');
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('tva', 10, 2)->default(0);
            $table->decimal('remise_fidelite', 10, 2)->default(0);
            $table->decimal('montant_ttc', 10, 2)->default(0);
            $table->dateTime('date_emission');
            $table->enum('statut', ['EN_ATTENTE', 'PAYEE', 'PARTIELLEMENT_PAYEE', 'ANNULEE'])->default('EN_ATTENTE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};