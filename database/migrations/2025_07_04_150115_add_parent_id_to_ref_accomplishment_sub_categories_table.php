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
        Schema::table('ref_accomplishment_sub_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->onDelete('cascade')
                ->after('accomplishment_sub_category_name')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_accomplishment_sub_categories', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropConstrainedForeignId('parent_id');
            // Then drop the column
            $table->dropColumn('parent_id');
        });
    }
};
