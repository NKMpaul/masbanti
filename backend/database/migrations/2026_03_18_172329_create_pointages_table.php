<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employe_id');
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('cascade');
            $table->date('date_pointage');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->enum('statut', ['PRESENT', 'ABSENT', 'EN_RETARD', 'CONGE'])->default('PRESENT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};