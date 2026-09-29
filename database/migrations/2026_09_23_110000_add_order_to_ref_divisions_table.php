<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ref_divisions', function (Blueprint $table) {
            $table->unsignedInteger('order')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('ref_divisions', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
