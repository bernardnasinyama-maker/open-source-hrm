<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FreshInstallSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe all operational data
        $tables = [
            "attendances","leaves","payrolls","site_expenses",
            "correspondences","correspondence_followups","tasks",
            "employee_documents","disciplinary_records","notifications",
            "activity_log","messages"
        ];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                echo "Cleared: {$table}\n";
            }
        }

        // Keep only system admin, wipe other employees
        DB::table("model_has_roles")
            ->whereIn("model_id",
                DB::table("employees")
                ->whereNotIn("employee_code",["SYS-001"])
                ->pluck("id"))
            ->delete();
        DB::table("employees")
            ->whereNotIn("employee_code",["SYS-001"])
            ->delete();

        echo "Fresh install complete - ready for new client!\n";
        echo "Run: php artisan db:seed --class=RolePermissionSeeder\n";
    }
}
