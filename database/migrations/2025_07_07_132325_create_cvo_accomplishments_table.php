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
        Schema::create('cvo_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->string('target');
            $table->foreignId('office_id')->comment('a.k.a role_id from roles or offices');
            $table->foreignId('ref_division_id')->comment('a.k.a division_id from divisions');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvo_accomplishments');
    }
};
