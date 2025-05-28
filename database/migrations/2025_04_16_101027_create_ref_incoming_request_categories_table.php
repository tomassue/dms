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
        Schema::create('ref_incoming_request_categories', function (Blueprint $table) {
            $table->id();
            $table->string('incoming_request_category_name');
            $table->foreignId('office_id')->comment('a.k.a role_id from roles or offices');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_incoming_request_categories');
    }
};
