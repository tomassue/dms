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
            $table->foreignId('ref_apo_meetings_category_id');
            $table->string('description');
            $table->time('time_start');
            $table->time('time_end')->nullable();
            $table->string('venue');
            $table->foreignId('prepared_by')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('noted_by')->nullable();
            $table->binary('file')->nullable();
            $table->foreignId('office_id')->comment('a.k.a role_id from roles or offices');
            $table->foreignId('ref_division_id')->nullable()->comment('a.k.a division_id from divisions');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('apo_minutes_of_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apo_meeting_id');
            $table->text('activity')->nullable();
            $table->text('point_person')->nullable();
            $table->text('expected_output')->nullable();
            $table->text('agreements')->nullable();
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
