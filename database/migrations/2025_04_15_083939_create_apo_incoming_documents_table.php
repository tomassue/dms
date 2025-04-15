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
        Schema::create('apo_incoming_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_document_id')->constrained('incoming_documents')->unique();
            $table->longText('source');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apo_incoming_documents');
    }
};
