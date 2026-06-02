<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add a composite unique constraint on (`incoming_request_category_name`, `office_id`).
     * This allows different offices to have categories with the same name,
     * while still preventing duplicates within the same office.
     */
    public function up(): void
    {
        Schema::table('ref_incoming_request_categories', function (Blueprint $table) {
            $table->unique(
                ['incoming_request_category_name', 'office_id'],
                'ref_incoming_req_categories_name_office_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_incoming_request_categories', function (Blueprint $table) {
            $table->dropUnique('ref_incoming_req_categories_name_office_unique');
        });
    }
};
