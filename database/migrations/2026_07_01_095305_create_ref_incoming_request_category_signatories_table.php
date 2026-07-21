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
        Schema::create('ref_incoming_request_category_signatories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ref_incoming_request_category_id');
            $table->foreign('ref_incoming_request_category_id', 'fk_cat_signatories_category_id')
                  ->references('id')->on('ref_incoming_request_categories')->cascadeOnDelete();
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
        Schema::dropIfExists('ref_incoming_request_category_signatories');
    }
};
