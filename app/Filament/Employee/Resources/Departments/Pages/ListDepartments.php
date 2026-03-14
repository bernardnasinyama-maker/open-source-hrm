<?php

namespace App\Filament\Employee\Resources\Departments\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Employee\Resources\Departments\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

}
