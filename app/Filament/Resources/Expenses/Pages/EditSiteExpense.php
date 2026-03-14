<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditSiteExpense extends EditRecord {
    protected static string $resource = SiteExpenseResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function mutateFormDataBeforeSave(array $data): array {
        $data["approved_by"] = in_array($data["status"] ?? "", ["approved","rejected"]) ? auth()->id() : null;
        return $data;
    }
}