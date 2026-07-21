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
        Schema::table('ref_incoming_request_categories', function (Blueprint $table) {
            $table->longText('pdf_template')->nullable()->after('office_id');
        });
    }

    public function down(): void
    {
        Schema::table('ref_incoming_request_categories', function (Blueprint $table) {
            $table->dropColumn('pdf_template');
        });
    }
};
