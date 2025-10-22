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
        Schema::table('files', function (Blueprint $table) {
            // 1. Add the new columns
            $table->string('file_path')->nullable()->after('type');
            $table->string('disk')->default('public')->after('file_path');

            // 2. Optional: Remove the old 'file' column if it stored content (BLOB/LONGBLOB)
            //    If your 'file' column is a large data type storing the actual file content, you should drop it 
            //    to clean up the database and prevent future bloat.
            // $table->dropColumn('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // Reverse the changes (IMPORTANT for rollback)
            $table->dropColumn('file_path');
            $table->dropColumn('disk');
            
            // Optional: Re-add the 'file' column if you dropped it in 'up()'
            // $table->longBlob('file')->nullable()->after('type'); // Use the original column definition
        });
    }
};
