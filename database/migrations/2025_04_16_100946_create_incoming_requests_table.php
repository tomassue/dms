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
        Schema::create('incoming_requests', function (Blueprint $table) {
            $table->id();
            $table->string('no')->unique();
            $table->string('office_barangay_organization');
            $table->date('date_requested');
            $table->foreignId('ref_incoming_request_category_id')->constrained('ref_incoming_request_categories');
            $table->dateTime('date_time');
            $table->string('contact_person_name');
            $table->string('contact_person_number');
            $table->text('description');
            $table->foreignId('ref_status_id')->constrained('ref_status');
            //TODO: Forwarded to will be a morph table
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_requests');
    }
};
