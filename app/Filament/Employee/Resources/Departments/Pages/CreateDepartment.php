<?php

namespace App\Filament\Employee\Resources\Departments\Pages;

use App\Filament\Employee\Resources\Departments\DepartmentResource;

use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

}
