<?php

namespace App\Filament\Resources\Documents;

use App\Models\EmployeeDocument;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|\UnitEnum|null $navigationGroup = 'HR Management';
    protected static ?string $navigationLabel = 'Documents';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Document';
    protected static ?string $pluralModelLabel = 'Documents';


    public static function canViewAny(): bool
    {
        return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Document Details')
                ->icon('heroicon-o-document')
                ->columns(2)
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(fn() => Employee::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('type')
                        ->label('Document Type')
                        ->options(EmployeeDocument::typeLabels())
                        ->required(),

                    TextInput::make('title')
                        ->label('Document Title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. John\'s Employment Contract 2025'),

                    DatePicker::make('expiry_date')
                        ->label('Expiry Date')
                        ->nullable()
                        ->helperText('Leave blank if document does not expire'),
                ]),

            Section::make('File Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Upload Document')
                        ->disk('public')
                        ->directory('employee-documents')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(10240)
                        ->required()
                        ->helperText('Accepted: PDF, JPG, PNG, DOC, DOCX (max 10MB)')
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $set('file_name', is_string($state) ? basename($state) : $state->getClientOriginalName());
                                $set('mime_type', is_string($state) ? '' : $state->getMimeType());
                                $set('file_size', is_string($state) ? '' : number_format($state->getSize() / 1024, 2) . ' KB');
                            }
                        }),

                    TextInput::make('file_name')->hidden(),
                    TextInput::make('mime_type')->hidden(),
                    TextInput::make('file_size')->hidden(),
                ]),

            Section::make('Verification & Notes')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Toggle::make('is_verified')
                        ->label('Mark as Verified')
                        ->helperText('Confirm document has been physically verified'),

                    Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpan(2)
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->label('Emp Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Document')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => EmployeeDocument::typeLabels()[$state] ?? $state)
                    ->color(fn($state) => match($state) {
                        'contract'        => 'primary',
                        'national_id'     => 'info',
                        'nssf_card'       => 'success',
                        'tin_certificate' => 'warning',
                        'medical_report'  => 'danger',
                        default           => 'gray',
                    }),

                TextColumn::make('expiry_date')
                    ->label('Expires')
                    ->date('d M Y')
                    ->color(fn($record) => $record?->isExpired() ? 'danger' : ($record?->isExpiringSoon() ? 'warning' : 'success'))
                    ->placeholder('No expiry'),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('file_size')
                    ->label('Size')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(EmployeeDocument::typeLabels())
                    ->label('Document Type'),

                SelectFilter::make('is_verified')
                    ->options([
                        '1' => 'Verified',
                        '0' => 'Not Verified',
                    ])
                    ->label('Verification Status'),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),

                    Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn(EmployeeDocument $record) =>
                            Storage::disk('public')->url($record->file_path)
                        )
                        ->openUrlInNewTab(),

                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'edit'   => Pages\EditEmployeeDocument::route('/{record}/edit'),
            'view'   => Pages\ViewEmployeeDocument::route('/{record}'),
        ];
    }
}
