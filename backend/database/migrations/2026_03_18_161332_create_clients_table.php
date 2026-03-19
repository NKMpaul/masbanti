<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->integer('points_fidelite')->default(0);
            $table->enum('niveau_fidelite', ['BRONZE', 'SILVER', 'GOLD', 'PLATINUM'])->default('BRONZE');
            $table->uuid('agence_principale_id')->nullable();
            $table->foreign('agence_principale_id')->references('id')->on('agences')->onDelete('set null');
            $table->boolean('notifications_email')->default(true);
            $table->boolean('notifications_sms')->default(false);
            $table->boolean('notifications_push')->default(true);
            $table->enum('mode_livraison_prefere', ['EN_AGENCE', 'LIVRAISON_DOMICILE'])->default('EN_AGENCE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};