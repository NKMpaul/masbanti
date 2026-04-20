<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('plannings', function (Blueprint $table) {
        $table->uuid('id')->primary(); // 👈 Norme UUID que tu utilises sur ton projet
        
        // Clé étrangère vers la table des utilisateurs (employés/livreurs)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->date('date_planning'); // Le jour concerné
        $table->time('heure_debut');   // Ex: 08:00
        $table->time('heure_fin');     // Ex: 17:00
        
        // Statuts : DISPONIBLE, EN_MISSION, CONGE, ABSENT
        $table->string('statut')->default('DISPONIBLE'); 
        
        $table->text('notes')->nullable(); // Pour justifier une absence ou un retard
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
