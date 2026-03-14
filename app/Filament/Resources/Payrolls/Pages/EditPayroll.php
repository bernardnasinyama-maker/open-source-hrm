<?php

namespace App\Filament\Resources\Payrolls\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Payrolls\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Notifications\PayslipNotification;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        if ($record->status === "paid") {
            $employee = $record->employee;
            if ($employee && $employee->email) {
                try {
                    $employee->notify(new PayslipNotification($record));
                } catch (\Exception $e) {
                    // Mail not configured yet - silent fail
                }
            }
        }
    }
}