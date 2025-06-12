<?php

use App\Models\RefDivision;
use App\Models\RefPosition;
use App\Models\User;
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
        Schema::create('ref_signatories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title');
            $table->foreignId('office_id');
            $table->foreignId('division_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_signatories');
    }
};
