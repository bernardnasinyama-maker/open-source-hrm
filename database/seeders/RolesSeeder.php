<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISSIONS ──────────────────────────────────────────────
        $permissions = [
            // Employees
            'view employees', 'create employees', 'edit employees', 'delete employees',
            // Attendance
            'view attendance', 'create attendance', 'edit attendance', 'delete attendance',
            // Leaves
            'view leaves', 'create leaves', 'edit leaves', 'delete leaves', 'approve leaves',
            // Payroll
            'view payroll', 'create payroll', 'edit payroll', 'delete payroll',
            // Documents
            'view documents', 'create documents', 'edit documents', 'delete documents',
            // Disciplinary
            'view disciplinary', 'create disciplinary', 'edit disciplinary', 'delete disciplinary',
            // Departments
            'view departments', 'create departments', 'edit departments', 'delete departments',
            // Reports
            'view reports', 'export reports', 'export full reports',
            // System
            'manage system', 'manage roles', 'manage admins',
            // Analytics
            'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'employee']);
        }

        // ── ROLES ─────────────────────────────────────────────────────

        // 1. SUPER ADMIN — Bernard — everything
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'employee']);
        $superAdmin->syncPermissions(Permission::where('guard_name', 'employee')->get());

        // 2. ADMIN — Chinese boss — all except system management
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'employee']);
        $admin->syncPermissions([
            'view employees','create employees','edit employees',
            'view attendance','create attendance','edit attendance',
            'view leaves','create leaves','edit leaves','approve leaves',
            'view payroll','create payroll','edit payroll',
            'view documents','create documents','edit documents',
            'view disciplinary','create disciplinary','edit disciplinary',
            'view departments','create departments','edit departments',
            'view reports','export reports','export full reports',
            'view analytics',
        ]);

        // 3. HR ASSISTANT — Ugandan HR — daily HR ops, no payroll delete/financials
        $hrAssistant = Role::firstOrCreate(['name' => 'hr_assistant', 'guard_name' => 'employee']);
        $hrAssistant->syncPermissions([
            'view employees','create employees','edit employees',
            'view attendance','create attendance','edit attendance',
            'view leaves','create leaves','edit leaves','approve leaves',
            'view documents','create documents','edit documents',
            'view disciplinary','create disciplinary',
            'view departments',
            'view reports','export reports',
            'view analytics',
        ]);

        // 4. VIEWER — read only
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'employee']);
        $viewer->syncPermissions([
            'view employees','view attendance','view leaves',
            'view documents','view disciplinary','view departments',
            'view reports','view analytics',
        ]);

        // 5. EMPLOYEE — self service only (keep existing)
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'employee']);
        $employee->syncPermissions([
            'view attendance','create attendance',
            'view leaves','create leaves',
            'view documents',
        ]);

        echo "Roles and permissions seeded successfully!\n";
        echo "Roles: super_admin, admin, hr_assistant, viewer, employee\n";
    }
}
