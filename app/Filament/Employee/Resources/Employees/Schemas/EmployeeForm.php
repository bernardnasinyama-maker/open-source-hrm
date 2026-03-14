<?php

namespace App\Filament\Employee\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_code'),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('national_id'),
                TextInput::make('kra_pin'),
                TextInput::make('emergency_contact_name'),
                TextInput::make('emergency_contact_phone')
                    ->tel(),
                DatePicker::make('date_of_birth'),
                TextInput::make('gender'),
                TextInput::make('marital_status'),
                TextInput::make('department_id')
                    ->numeric(),
                TextInput::make('position_id')
                    ->numeric(),
                TextInput::make('employment_type')
                    ->required(),
                DatePicker::make('hire_date'),
                DatePicker::make('termination_date'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('next_of_kin_name'),
                TextInput::make('next_of_kin_relationship'),
                TextInput::make('next_of_kin_phone')
                    ->tel(),
                TextInput::make('next_of_kin_email')
                    ->email(),
            ]);
    }
}