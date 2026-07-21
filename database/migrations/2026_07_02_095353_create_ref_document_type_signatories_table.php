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
        Schema::create('ref_document_type_signatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ref_document_type_id');
            $table->foreign('ref_document_type_id', 'fk_doc_type_signatories_type_id')
                  ->references('id')->on('ref_document_type')->cascadeOnDelete();
            $table->string('name');
            $table->string('title');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_document_type_signatories');
    }
};
