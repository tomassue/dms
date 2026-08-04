<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Roles and Permissions ---\n";
foreach (\Spatie\Permission\Models\Role::all() as $role) {
    echo "Role: {$role->name}\n";
    echo "Permissions: " . implode(', ', $role->permissions->pluck('name')->toArray()) . "\n\n";
}
