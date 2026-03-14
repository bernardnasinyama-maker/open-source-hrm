<?php
namespace App\Filament\Resources\Documents\Pages;
use App\Filament\Resources\Documents\EmployeeDocumentResource;
use Filament\Resources\Pages\EditRecord;
class EditEmployeeDocument extends EditRecord
{
    protected static string $resource = EmployeeDocumentResource::class;
}
