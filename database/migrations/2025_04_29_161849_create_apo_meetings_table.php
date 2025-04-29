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
        Schema::create('apo_meetings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description');
            $table->time('time_start');
            $table->time('time_end');
            $table->string('venue');
            $table->foreignId('prepared_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('noted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('apo_minutes_of_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id');
            $table->text('activity');
            $table->text('point_person');
            $table->text('expected_output');
            $table->text('agreements');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apo_meetings');
        Schema::dropIfExists('apo_minutes_of_meetings');
    }
};
