<?php

namespace App\Filament\Employee\Resources\Leaves;

use App\Filament\Employee\Resources\Leaves\Pages\CreateLeave;
use App\Filament\Employee\Resources\Leaves\Pages\EditLeave;
use App\Filament\Employee\Resources\Leaves\Pages\ListLeaves;
use App\Filament\Employee\Resources\Leaves\Pages\ViewLeave;
use App\Filament\Employee\Resources\Leaves\Schemas\LeaveForm;
use App\Filament\Employee\Resources\Leaves\Schemas\LeaveInfolist;
use App\Filament\Employee\Resources\Leaves\Tables\LeavesTable;
use App\Models\Leave;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return LeaveForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeaveInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeavesTable::configure($table);
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
            'index' => ListLeaves::route('/'),
            'create' => CreateLeave::route('/create'),
            'view' => ViewLeave::route('/{record}'),
            'edit' => EditLeave::route('/{record}/edit'),
        ];
    }
}
