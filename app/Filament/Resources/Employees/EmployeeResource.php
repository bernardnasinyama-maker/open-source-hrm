<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Schemas\EmployeeTable;
use Filament\Schemas\Schema;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 1;


    public static function canViewAny(): bool
    {
        return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false;
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
        return EmployeeForm::configure($schema);
    }

    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutRole(["super_admin", "viewer"])
            ->where("employee_code", "!=", "SYS-001")
            ->where("employee_code", "!=", "CRBC-VIEW");
    }
    public static function table(Table $table): Table
    {
        return EmployeeTable::configure($table)
            ->modifyQueryUsing(
                function (Builder $query) {
                    $query->withoutRole(['super_admin'])->where('employee_code','!=','SYS-001');
                }
            )
        ;
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
            'index' => ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
