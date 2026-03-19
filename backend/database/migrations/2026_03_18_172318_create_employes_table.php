<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('cin')->unique();
            $table->string('telephone')->nullable();
            $table->string('email')->unique();
            $table->enum('poste', [
                'RECEPTIONNISTE',
                'OPERATEUR_NETTOYAGE',
                'REPASSEUR',
                'LIVREUR',
                'GERANT'
            ]);
            $table->uuid('agence_id');
            $table->foreign('agence_id')->references('id')->on('agences')->onDelete('cascade');
            $table->date('date_embauche');
            $table->enum('statut', ['ACTIF', 'INACTIF', 'EN_CONGE'])->default('ACTIF');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};