<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ref_accomplishment_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Role::class); //* Creator of accomplishment category. If the superadmin, it can be selected which role it will be associated with.
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_accomplishment_categories');
    }
};
