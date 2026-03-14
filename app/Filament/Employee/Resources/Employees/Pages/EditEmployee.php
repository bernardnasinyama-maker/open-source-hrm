<?php

namespace App\Filament\Employee\Resources\Employees\Pages;

use App\Filament\Employee\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn ($record) => $record->email !== 'bernardnasinyama@gmail.com'),
            ForceDeleteAction::make()
                ->visible(fn ($record) => $record->email !== 'bernardnasinyama@gmail.com'),
            RestoreAction::make()
                ->visible(fn ($record) => $record->email !== 'bernardnasinyama@gmail.com'),
        ];
    }
}
