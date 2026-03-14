<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSiteExpenses extends ListRecords {
    protected static string $resource = SiteExpenseResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}