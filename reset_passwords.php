<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$shi = App\Models\Employee::find(3);
$shi->password = Illuminate\Support\Facades\Hash::make("crbc2026");
$shi->save();
echo "Mr Shi password: crbc2026\n";

$thembo = App\Models\Employee::find(2);
$thembo->password = Illuminate\Support\Facades\Hash::make("crbc2026");
$thembo->save();
echo "Thembo password: crbc2026\n";
