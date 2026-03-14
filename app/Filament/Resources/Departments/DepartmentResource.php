<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Schemas\DepartmentTable;
use Filament\Schemas\Schema;
use App\Filament\Resources\Departments\Pages\ListDepartments;
use App\Models\Department;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Departments\Schemas\DepartmentForm;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';


    public static function canViewAny(): bool
    {
        return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin"]) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin"]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return DepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            // 'create' => Pages\CreateDepartment::route('/create'),
            // 'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

}
