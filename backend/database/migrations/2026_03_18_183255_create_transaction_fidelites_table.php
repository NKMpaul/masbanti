<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_fidelites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->enum('type', ['GAIN', 'DEBIT']);
            $table->integer('points');
            $table->string('motif')->nullable();
            $table->uuid('commande_id')->nullable();
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_fidelites');
    }
};