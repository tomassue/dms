<?php

use App\Models\Accomplishment;
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
        Schema::create('apo_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Accomplishment::class);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('next_steps');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apo_accomplishments');
    }
};
