<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add a composite unique constraint on (`outgoing_category_name`, `office_id`).
     * This allows different offices to have categories with the same name,
     * while still preventing duplicates within the same office.
     */
    public function up(): void
    {
        Schema::table('ref_outgoing_categories', function (Blueprint $table) {
            // Add composite unique: same name allowed across different offices
            $table->unique(['outgoing_category_name', 'office_id'], 'ref_outgoing_categories_name_office_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_outgoing_categories', function (Blueprint $table) {
            $table->dropUnique('ref_outgoing_categories_name_office_unique');
        });
    }
};

