<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_livraisons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('commande_id');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->uuid('livreur_id');
            $table->foreign('livreur_id')->references('id')->on('employes')->onDelete('cascade');
            $table->enum('type', ['COLLECTE', 'LIVRAISON']);
            $table->string('adresse');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->dateTime('creneau_debut')->nullable();
            $table->dateTime('creneau_fin')->nullable();
            $table->enum('statut', ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ECHOUEE'])->default('PLANIFIEE');
            $table->text('signature_client')->nullable();
            $table->string('photo_preuve')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_livraisons');
    }
};