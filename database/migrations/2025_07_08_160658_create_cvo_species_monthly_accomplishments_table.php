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
        Schema::create('cvo_species_monthly_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cvo_accomplishment_id')
                ->constrained('cvo_accomplishments')
                ->onDelete('cascade')
                ->name('cvo_sm_acc_fk'); // Shorter name

            $table->foreignId('species_id')
                ->constrained('ref_species')
                ->onDelete('cascade')
                ->name('cvo_sm_species_fk'); // Shorter name

            $table->integer('month'); // Month number (1-12)
            $table->integer('accomplished_value')->nullable();
            $table->text('remarks')->nullable();

            // Shorter unique constraint name
            $table->unique(['cvo_accomplishment_id', 'species_id', 'month'], 'cvo_sm_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvo_species_monthly_accomplishments');
    }
};
