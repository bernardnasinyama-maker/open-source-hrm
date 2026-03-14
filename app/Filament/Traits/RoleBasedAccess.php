<?php
namespace App\Filament\Traits;

trait RoleBasedAccess
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(static::$allowedRoles ?? ['super_admin']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(static::$createRoles ?? static::$allowedRoles ?? ['super_admin']) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(static::$editRoles ?? static::$allowedRoles ?? ['super_admin']) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(static::$deleteRoles ?? ['super_admin']) ?? false;
    }
}
