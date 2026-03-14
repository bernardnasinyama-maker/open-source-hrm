<?php

namespace App\Filament\Employee\Resources\Payrolls\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PayrollInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('pay_date')
                    ->date(),
                TextEntry::make('period'),
                TextEntry::make('gross_pay')
                    ->numeric(),
                TextEntry::make('net_pay')
                    ->numeric(),
                TextEntry::make('deductions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('allowances')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('bonuses')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
