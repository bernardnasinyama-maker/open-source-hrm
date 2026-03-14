<?php

namespace App\Filament\Resources\Payrolls\Schema;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use App\Models\Employee;
use Filament\Actions\{BulkActionGroup, DeleteBulkAction};

class PayrollTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->label('Emp Code')
                    ->sortable()
                    ->searchable()
                    ->tooltip('Employee Code'),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                TextColumn::make('period')
                    ->label('Period')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pay_date')
                    ->date('d M Y')
                    ->label('Pay Date')
                    ->sortable(),

                TextColumn::make('gross_pay')
                    ->label('Gross Pay')
                    ->sortable()
                    ->money('UGX')
                    ->color('gray'),

                TextColumn::make('deductions')
                    ->label('PAYE')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) $state = json_decode($state, true);
                        $paye = $state['PAYE'] ?? 0;
                        return 'UGX ' . number_format((float)$paye);
                    })
                    ->color('danger')
                    ->tooltip('Pay As You Earn (URA)'),

                TextColumn::make('deductions')
                    ->label('NSSF')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) $state = json_decode($state, true);
                        $nssf = $state['NSSF Employee'] ?? 0;
                        return 'UGX ' . number_format((float)$nssf);
                    })
                    ->color('warning')
                    ->tooltip('NSSF Employee Contribution (5%)')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('net_pay')
                    ->label('Net Pay')
                    ->sortable()
                    ->money('UGX')
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Filter::make('employee')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(fn() => Employee::all()->pluck('name', 'id'))
                            ->searchable(),
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
