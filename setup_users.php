<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app()[Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

$bernard = App\Models\Employee::find(1);
$bernard->first_name     = "Eng. Bernard";
$bernard->last_name      = "Nasinyama";
$bernard->email          = "bernardnasinyama@gmail.com";
$bernard->employee_code  = "CRBC-001";
$bernard->gender         = "Male";
$bernard->employment_type = "Permanent";
$bernard->is_active      = true;
$bernard->password       = Illuminate\Support\Facades\Hash::make("bernie");
$bernard->save();
$r = Spatie\Permission\Models\Role::where("name","super_admin")->where("guard_name","web")->first();
$bernard->roles()->sync([$r->id]);
echo "Bernard OK - password: bernie\n";

$v = App\Models\Employee::firstOrNew(["email"=>"viewer@crbc.com"]);
$v->first_name     = "CRBC";
$v->last_name      = "Viewer";
$v->employee_code  = "CRBC-VIEW";
$v->gender         = "Male";
$v->employment_type = "Permanent";
$v->is_active      = true;
$v->password       = Illuminate\Support\Facades\Hash::make("viewer123");
$v->save();
$r2 = Spatie\Permission\Models\Role::where("name","viewer")->where("guard_name","web")->first();
$v->roles()->sync([$r2->id]);
echo "Viewer OK - viewer@crbc.com / viewer123\n";

$shi = App\Models\Employee::find(3);
$r3 = Spatie\Permission\Models\Role::where("name","admin")->where("guard_name","web")->first();
$shi->roles()->sync([$r3->id]);
echo "Mr Shi = admin OK\n";

$thembo = App\Models\Employee::find(2);
$r4 = Spatie\Permission\Models\Role::where("name","hr_assistant")->where("guard_name","web")->first();
$thembo->roles()->sync([$r4->id]);
echo "Thembo = hr_assistant OK\n";
