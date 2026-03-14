<?php
namespace App\Filament\Employee\Resources\Correspondences\Pages;
use App\Filament\Employee\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
class EditCorrespondence extends EditRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [ViewAction::make(), DeleteAction::make()]; }
}