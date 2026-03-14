<?php

namespace App\Filament\Resources\Payrolls\Schema;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Employee;
use App\Services\UgandaPayrollCalculator;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── EMPLOYEE & PERIOD ────────────────────────────────────
            Section::make('Employee & Period')
                ->icon('heroicon-o-user')
                ->columns(3)
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(fn() => Employee::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    DatePicker::make('pay_date')
                        ->label('Pay Date')
                        ->required(),

                    TextInput::make('period')
                        ->label('Period')
                        ->placeholder('e.g., 2025-03')
                        ->required()
                        ->maxLength(7),
                ]),

            // ── GROSS PAY ────────────────────────────────────────────
            Section::make('Gross Pay & Allowances')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    TextInput::make('gross_pay')
                        ->label('Basic / Gross Pay (UGX)')
                        ->required()
                        ->numeric()
                        ->prefix('UGX')
                        ->live(debounce: 600)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),

                    KeyValue::make('allowances')
                        ->label('Allowances')
                        ->keyLabel('Type (e.g. Transport, Housing)')
                        ->valueLabel('Amount (UGX)')
                        ->reorderable()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),
                ]),

            // ── UGANDA STATUTORY DEDUCTIONS (auto-computed) ──────────
            Section::make('🇺🇬 Uganda Statutory Deductions (Auto-Calculated)')
                ->icon('heroicon-o-calculator')
                ->columns(2)
                ->description('Automatically computed from gross pay per URA & NSSF Act 2014')
                ->schema([
                    TextInput::make('paye_amount')
                        ->label('PAYE (Pay As You Earn)')
                        ->prefix('UGX')
                        ->numeric()
                        ->readOnly()
                        ->helperText('URA graduated tax — 0% to 40%'),

                    TextInput::make('nssf_employee_amount')
                        ->label('NSSF Employee (5%)')
                        ->prefix('UGX')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Employee contributes 5% of gross'),

                    TextInput::make('nssf_employer_amount')
                        ->label('NSSF Employer (10%) — for records')
                        ->prefix('UGX')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Employer contributes 10% — not deducted from employee'),

                    TextInput::make('lst_amount')
                        ->label('LST (Local Service Tax)')
                        ->prefix('UGX')
                        ->numeric()
                        ->readOnly()
                        ->helperText('Monthly equivalent of annual LST tier'),
                ]),

            // ── ADDITIONAL DEDUCTIONS & BONUSES ─────────────────────
            Section::make('Additional Deductions & Bonuses')
                ->icon('heroicon-o-adjustments-horizontal')
                ->columns(2)
                ->schema([
                    KeyValue::make('deductions')
                        ->label('Other Deductions')
                        ->keyLabel('Type (e.g. Loan, Advance)')
                        ->valueLabel('Amount (UGX)')
                        ->reorderable()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),

                    KeyValue::make('bonuses')
                        ->label('Bonuses')
                        ->keyLabel('Type (e.g. Performance)')
                        ->valueLabel('Amount (UGX)')
                        ->reorderable()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::recalculate($get, $set);
                        }),
                ]),

            // ── NET PAY SUMMARY ──────────────────────────────────────
            Section::make('💰 Net Pay Summary')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    TextInput::make('total_allowances_display')
                        ->label('Total Allowances')
                        ->prefix('UGX')
                        ->readOnly()
                        ->numeric(),

                    TextInput::make('total_deductions_display')
                        ->label('Total Deductions')
                        ->prefix('UGX')
                        ->readOnly()
                        ->numeric(),

                    TextInput::make('net_pay')
                        ->label('NET PAY (Take Home)')
                        ->prefix('UGX')
                        ->readOnly()
                        ->numeric()
                        ->extraAttributes(['style' => 'font-weight:700;font-size:1.1rem;color:#16a34a;']),
                ]),

            // ── STATUS & NOTES ───────────────────────────────────────
            Section::make('Status & Notes')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required(),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpan(1),
                ]),

        ]);
    }

    // ── Auto-calculation logic ───────────────────────────────────────

    private static function recalculate(Get $get, Set $set): void
    {
        $gross = (float) ($get('gross_pay') ?? 0);

        if ($gross <= 0) return;

        $calc = UgandaPayrollCalculator::calculate($gross);

        // Set statutory fields
        $set('paye_amount',          $calc['paye']);
        $set('nssf_employee_amount', $calc['nssf_employee']);
        $set('nssf_employer_amount', $calc['nssf_employer']);
        $set('lst_amount',           $calc['lst']);

        // Sum allowances
        $allowances = collect($get('allowances') ?? [])->sum(fn($v) => (float) $v);

        // Sum other deductions
        $otherDeductions = collect($get('deductions') ?? [])->sum(fn($v) => (float) $v);

        // Sum bonuses
        $bonuses = collect($get('bonuses') ?? [])->sum(fn($v) => (float) $v);

        $totalStatutory   = $calc['paye'] + $calc['nssf_employee'] + $calc['lst'];
        $totalDeductions  = $totalStatutory + $otherDeductions;
        $totalAllowances  = $allowances + $bonuses;
        $netPay           = $gross - $totalDeductions + $totalAllowances;

        $set('total_allowances_display', round($totalAllowances, 2));
        $set('total_deductions_display', round($totalDeductions, 2));
        $set('net_pay', round($netPay, 2));

        // Store statutory deductions inside the deductions JSON
        $currentDeductions = $get('deductions') ?? [];
        $currentDeductions['PAYE']         = $calc['paye'];
        $currentDeductions['NSSF Employee'] = $calc['nssf_employee'];
        $currentDeductions['LST']           = $calc['lst'];
        $set('deductions', $currentDeductions);
    }
}
