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
        Schema::create('cvo_period_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cvo_accomplishment_id');
            $table->string('targetable_type');
            $table->unsignedBigInteger('targetable_id');
            $table->index(['targetable_type', 'targetable_id'], 'cvo_p_t_poly_idx'); // Shorter, custom name
            $table->integer('target_value')->default(0);

            $table->timestamps();

            $table->unique(['cvo_accomplishment_id', 'targetable_type', 'targetable_id'], name: 'unique_period_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvo_period_targets');
    }
};
