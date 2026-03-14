<?php
namespace App\Filament\Employee\Resources\Correspondences\Pages;
use App\Filament\Employee\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
class ViewCorrespondence extends ViewRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}