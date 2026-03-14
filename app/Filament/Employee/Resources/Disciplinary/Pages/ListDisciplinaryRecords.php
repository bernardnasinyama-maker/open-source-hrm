<?php
namespace App\Filament\Employee\Resources\Disciplinary\Pages;
use App\Filament\Employee\Resources\Disciplinary\DisciplinaryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
class ListDisciplinaryRecords extends ListRecords
{
    protected static string $resource = DisciplinaryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
