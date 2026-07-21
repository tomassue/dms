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
            $table->unsignedTinyInteger('signatory_font_size')->default(12)->after('pdf_header_image');
        });
    }

    public function down(): void
    {
        Schema::table('ref_document_type', function (Blueprint $table) {
            $table->dropColumn('signatory_font_size');
        });
    }
};
