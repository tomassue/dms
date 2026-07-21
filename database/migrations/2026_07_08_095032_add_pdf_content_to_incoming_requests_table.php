<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incoming_requests', function (Blueprint $table) {
            $table->json('pdf_content')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('incoming_requests', function (Blueprint $table) {
            $table->dropColumn('pdf_content');
        });
    }
};
