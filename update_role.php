<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$user = User::where('email', 'Raihan@gmail.com')->first();
if ($user) {
    // Ensure role exists
    Role::firstOrCreate(['name' => 'superadmin']);
    
    $user->syncRoles(['superadmin']);
    $user->role = 'superadmin';
    $user->save();
    
    echo "User " . $user->name . " is now superadmin.\n";
} else {
    echo "User not found\n";
}
