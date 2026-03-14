<?php
namespace App\Filament\Employee\Resources\Correspondences\Pages;
use App\Filament\Employee\Resources\Correspondences\CorrespondenceResource;
use Filament\Resources\Pages\CreateRecord;
class CreateCorrespondence extends CreateRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data["created_by"] = auth()->id();
        return $data;
    }
}