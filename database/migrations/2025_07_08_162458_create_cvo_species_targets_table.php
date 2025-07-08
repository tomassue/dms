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
        Schema::create('cvo_species_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cvo_accomplishment_id')->constrained('cvo_accomplishments')->onDelete('cascade');
            $table->foreignId('ref_species_id')->constrained('ref_species')->onDelete('cascade');
            $table->decimal('target_value', 10, 2)->nullable();

            $table->unique(['cvo_accomplishment_id', 'ref_species_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvo_species_targets');
    }
};
