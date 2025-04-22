<?php

use App\Models\RefIncomingDocumentCategory;
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
        Schema::create('incoming_documents', function (Blueprint $table) {
            $table->id();
            //// $table->foreignIdFor(RefIncomingDocumentCategory::class);
            $table->foreignId('ref_incoming_document_category_id')->constrained('ref_incoming_documents_categories');
            $table->string('document_info');
            $table->date('date');
            $table->longText('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_documents');
    }
};
