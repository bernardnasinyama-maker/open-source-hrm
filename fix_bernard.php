<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update existing Bernard (ID 1) to be the SYSTEM super_admin account
// Change email to einsteinbernard3000 so it does not appear as a regular employee
$system = App\Models\Employee::find(1);
$system->first_name     = "System";
$system->last_name      = "Administrator";
$system->email          = "einsteinbernard3000@gmail.com";
$system->employee_code  = "SYS-001";
$system->employment_type = "Permanent";
$system->is_active      = true;
$system->password       = Illuminate\Support\Facades\Hash::make("bernie");
$system->save();
echo "System admin updated: einsteinbernard3000@gmail.com / bernie\n";

// Create Bernard as a proper employee
$bernard = App\Models\Employee::firstOrNew(["email" => "bernardnasinyama@gmail.com"]);
$bernard->first_name     = "Eng. Bernard";
$bernard->last_name      = "Nasinyama";
$bernard->email          = "bernardnasinyama@gmail.com";
$bernard->employee_code  = "CRBC-ENG-001";
$bernard->gender         = "Male";
$bernard->employment_type = "Permanent";
$bernard->is_active      = true;
$bernard->password       = Illuminate\Support\Facades\Hash::make("bernie");
$bernard->save();
$empRole = Spatie\Permission\Models\Role::where("name","employee")->where("guard_name","web")->first();
$bernard->roles()->sync([$empRole->id]);
echo "Bernard employee created: bernardnasinyama@gmail.com / bernie\n";

