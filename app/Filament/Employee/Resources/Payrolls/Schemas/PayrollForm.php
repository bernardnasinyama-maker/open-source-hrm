<?php

namespace App\Filament\Employee\Resources\Payrolls\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('pay_date')
                    ->required(),
                TextInput::make('period')
                    ->required(),
                TextInput::make('gross_pay')
                    ->required()
                    ->numeric(),
                TextInput::make('net_pay')
                    ->required()
                    ->numeric(),
                Textarea::make('deductions')
                    ->columnSpanFull(),
                Textarea::make('allowances')
                    ->columnSpanFull(),
                Textarea::make('bonuses')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
