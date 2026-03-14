<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class RolePermissionSeeder extends Seeder {
    public function run(): void {
        foreach (["super_admin","admin","hr_assistant","viewer","employee"] as $role) {
            Role::firstOrCreate(["name" => $role, "guard_name" => "web"]);
        }
        echo "Roles created: " . Role::count() . PHP_EOL;
    }
}