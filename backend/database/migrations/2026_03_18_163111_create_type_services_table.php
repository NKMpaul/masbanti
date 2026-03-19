<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('description')->nullable();
            $table->decimal('coefficient_prix', 5, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_services');
    }
};