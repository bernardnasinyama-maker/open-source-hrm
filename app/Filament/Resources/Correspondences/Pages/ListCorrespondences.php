<?php
namespace App\Filament\Resources\Correspondences\Pages;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListCorrespondences extends ListRecords {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}