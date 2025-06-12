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
        Schema::create('users_metadata', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->unique();
            $table->enum('is_office_admin', [0, 1])->nullable();
            $table->foreignIdFor(RefDivision::class);
            $table->foreignIdFor(RefPosition::class);
            $table->string('phone_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_metadata');
    }
};
