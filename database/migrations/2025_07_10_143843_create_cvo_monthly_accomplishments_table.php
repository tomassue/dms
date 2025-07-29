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
        Schema::create('cvo_monthly_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cvo_accomplishment_id')->constrained('cvo_accomplishments')->onDelete('cascade');
            $table->string('accomplishable_type'); // 'App\Models\RefAccomplishmentCategory', 'App\Models\RefAccomplishmentSubcategory', 'App\Models\RefSpecies'
            $table->unsignedBigInteger('accomplishable_id');
            $table->index(['accomplishable_type', 'accomplishable_id'], 'cvo_m_a_poly_idx'); // Shorter, custom name
            $table->tinyInteger('month'); // 1-12 for January-December
            $table->integer('accomplished_value')->nullable(); // Can be null if not yet entered
            $table->text('remarks')->nullable();
            $table->integer('office_id')->comment('a.k.a role_id from roles or offices');
            $table->integer('ref_division_id')->comment('a.k.a division_id from divisions');
            $table->integer('user_id')->comment('a.k.a user_id from users');
            $table->timestamps();

            // Add a unique constraint to prevent duplicate monthly records for the same entity in the same month/period
            $table->unique(['cvo_accomplishment_id', 'accomplishable_type', 'accomplishable_id', 'month'], 'unique_monthly_accomplishment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvo_monthly_accomplishments');
    }
};
