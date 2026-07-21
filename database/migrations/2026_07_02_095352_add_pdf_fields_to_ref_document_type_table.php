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
        Schema::table('ref_document_type', function (Blueprint $table) {
            $table->longText('pdf_template')->nullable()->after('office_id');
            $table->string('pdf_header_image')->nullable()->after('pdf_template');
        });
    }

    public function down(): void
    {
        Schema::table('ref_document_type', function (Blueprint $table) {
            $table->dropColumn(['pdf_template', 'pdf_header_image']);
        });
    }
};
