<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate([
            'name' => 'backup.read',
            'guard_name' => 'web',
        ]);
    }

    public function down(): void
    {
        Permission::where('name', 'backup.read')
            ->where('guard_name', 'web')
            ->delete();
    }
};
