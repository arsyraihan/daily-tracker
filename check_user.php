<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'Raihan@gmail.com')->first();
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Role Field: " . $user->role . "\n";
    echo "Spatie Roles:\n";
    foreach ($user->getRoleNames() as $role) {
        echo "- " . $role . "\n";
    }
    echo "Permissions:\n";
    foreach ($user->getAllPermissions() as $permission) {
        echo "- " . $permission->name . "\n";
    }
} else {
    echo "User not found\n";
}
