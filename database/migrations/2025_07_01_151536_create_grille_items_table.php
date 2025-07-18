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
        Schema::create('grille_items', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->integer('base_notation');
            $table->foreignId('grille_label_id')->constrained('grille_labels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grille_items');
    }
};
