<?php
namespace App\Filament\Employee\Resources\Expenses\Pages;
use App\Filament\Employee\Resources\Expenses\SiteExpenseResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSiteExpense extends CreateRecord {
    protected static string $resource = SiteExpenseResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data["created_by"] = auth()->id();
        return $data;
    }
}