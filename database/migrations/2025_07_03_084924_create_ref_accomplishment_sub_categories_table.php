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
        Schema::create('ref_accomplishment_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->nullable()->comment('For sorting purposes');
            $table->string('accomplishment_sub_category_name');
            $table->enum('is_inputtable', ['Y', 'N'])->nullable(); // Give provision for other offices that doesn't require this field.
            $table->integer('parent_id')->nullable();
            $table->foreignId('ref_accomplishment_category_id');
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
        Schema::dropIfExists('ref_accomplishment_sub_categories');
    }
};
