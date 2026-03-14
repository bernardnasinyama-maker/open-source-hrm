<?php

namespace App\Filament\Resources\Leaves\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Leaves\LeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Notifications\LeaveStatusNotification;

class EditLeave extends EditRecord
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $status = $record->status;
        if (in_array($status, ["approved", "rejected"])) {
            $employee = $record->employee;
            if ($employee && $employee->email) {
                try {
                    $employee->notify(new LeaveStatusNotification($record, $status));
                } catch (\Exception $e) {
                    // Mail not configured yet - silent fail
                }
            }
        }
    }
}