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
            $table->foreignIdFor(User::class); # Get name from users but when we retrieve it, users should only see records associated with their role (office).
            $table->foreignIdFor(RefPosition::class);
            $table->foreignIdFor(RefDivision::class);
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
