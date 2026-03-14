<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSiteExpense extends CreateRecord {
    protected static string $resource = SiteExpenseResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data["created_by"] = auth()->id();
        return $data;
    }
}