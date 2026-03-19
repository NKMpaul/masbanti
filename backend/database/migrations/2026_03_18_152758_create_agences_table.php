<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('adresse');
            $table->string('ville');
            $table->string('telephone');
            $table->string('email');
            $table->string('horaire_ouverture')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('statut', ['ACTIVE', 'INACTIVE', 'EN_MAINTENANCE'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};