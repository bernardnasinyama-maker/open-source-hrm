<?php
namespace App\Filament\Resources\Documents\Pages;
use App\Filament\Resources\Documents\EmployeeDocumentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
class ListEmployeeDocuments extends ListRecords
{
    protected static string $resource = EmployeeDocumentResource::class;
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
