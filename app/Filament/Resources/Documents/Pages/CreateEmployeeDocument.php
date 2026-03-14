<?php
namespace App\Filament\Resources\Documents\Pages;
use App\Filament\Resources\Documents\EmployeeDocumentResource;
use Filament\Resources\Pages\CreateRecord;
class CreateEmployeeDocument extends CreateRecord
{
    protected static string $resource = EmployeeDocumentResource::class;
}
