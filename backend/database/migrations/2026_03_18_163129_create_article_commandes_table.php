<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_commandes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('commande_id');
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
            $table->uuid('type_article_id');
            $table->foreign('type_article_id')->references('id')->on('type_articles')->onDelete('cascade');
            $table->string('description')->nullable();
            $table->integer('quantite')->default(1);
            $table->decimal('prix_unitaire', 10, 2);
            $table->string('code_barre_etiquette')->nullable();
            $table->enum('etat', ['NORMAL', 'TACHE', 'DECOLORE', 'ABIME'])->default('NORMAL');
            $table->string('photo_avant')->nullable();
            $table->string('photo_apres')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_commandes');
    }
};