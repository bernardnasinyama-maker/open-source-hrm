<?php

namespace App\Filament\Employee\Resources\Employees\Pages;

use App\Filament\Employee\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
