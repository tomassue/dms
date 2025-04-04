<?php

use App\Models\RefAccomplishmentCategory;
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
        Schema::create('accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RefAccomplishmentCategory::class);
            $table->date('date');
            $table->text('details');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accomplishments');
    }
};
