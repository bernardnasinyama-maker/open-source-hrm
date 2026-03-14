<?php
namespace App\Filament\Employee\Resources\Disciplinary;

use App\Models\DisciplinaryRecord;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;

class DisciplinaryResource extends Resource
{
    protected static ?string $model = DisciplinaryRecord::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static string|\UnitEnum|null $navigationGroup = 'HR Management';
    protected static ?string $navigationLabel = 'Disciplinary';
    protected static ?int $navigationSort = 6;
    protected static ?string $modelLabel = 'Disciplinary Record';
    protected static ?string $pluralModelLabel = 'Disciplinary Records';


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
        return auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin"]) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasAnyRole(["super_admin"]) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Incident Details')
                ->icon('heroicon-o-exclamation-triangle')
                ->columns(2)
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(fn() => Employee::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Select::make('raised_by')
                        ->label('Raised By (HR Officer)')
                        ->options(fn() => Employee::all()->pluck('name', 'id'))
                        ->default(fn() => Auth::id())
                        ->required(),

                    DatePicker::make('incident_date')
                        ->label('Incident Date')
                        ->required()
                        ->default(now()),

                    DatePicker::make('review_date')
                        ->label('Review Date')
                        ->nullable(),

                    Select::make('type')
                        ->label('Disciplinary Type')
                        ->options(DisciplinaryRecord::typeLabels())
                        ->required(),

                    Select::make('severity')
                        ->label('Severity Level')
                        ->options(DisciplinaryRecord::severityLabels())
                        ->required(),

                    TextInput::make('subject')
                        ->label('Subject / Title')
                        ->required()
                        ->columnSpan(2)
                        ->placeholder('e.g. Unauthorized Absence on 10 March 2026'),
                ]),

            Section::make('Incident Report')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Textarea::make('incident_description')
                        ->label('Incident Description')
                        ->required()
                        ->rows(4)
                        ->placeholder('Describe the incident in full detail...'),

                    Textarea::make('action_taken')
                        ->label('Action Taken')
                        ->required()
                        ->rows(3)
                        ->placeholder('What action was taken against the employee...'),

                    Textarea::make('outcome')
                        ->label('Outcome / Resolution')
                        ->rows(3)
                        ->nullable()
                        ->placeholder('Final outcome or resolution of the matter...'),
                ]),

            Section::make('Status & Acknowledgement')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'open'         => 'Open',
                            'under_review' => 'Under Review',
                            'resolved'     => 'Resolved',
                            'appealed'     => 'Appealed',
                        ])
                        ->default('open')
                        ->required(),

                    Toggle::make('employee_acknowledged')
                        ->label('Employee Acknowledged')
                        ->helperText('Has the employee signed/acknowledged this record?'),

                    FileUpload::make('supporting_document')
                        ->label('Supporting Document')
                        ->disk('public')
                        ->directory('disciplinary-docs')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->nullable()
                        ->helperText('Upload any supporting evidence (PDF/Image, max 5MB)'),

                    Textarea::make('notes')
                        ->label('Additional Notes')
                        ->nullable()
                        ->rows(2),
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

                TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => DisciplinaryRecord::typeLabels()[$state] ?? $state)
                    ->color(fn($state) => match($state) {
                        'verbal_warning'  => 'info',
                        'written_warning' => 'warning',
                        'final_warning'   => 'warning',
                        'suspension'      => 'danger',
                        'termination'     => 'danger',
                        default           => 'gray',
                    }),

                TextColumn::make('severity')
                    ->label('Severity')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'minor'    => 'success',
                        'moderate' => 'info',
                        'serious'  => 'warning',
                        'gross'    => 'danger',
                    }),

                TextColumn::make('incident_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'open'         => 'warning',
                        'under_review' => 'info',
                        'resolved'     => 'success',
                        'appealed'     => 'danger',
                    }),

                IconColumn::make('employee_acknowledged')
                    ->label('Acknowledged')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('raisedBy.name')
                    ->label('Raised By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')->options(DisciplinaryRecord::typeLabels()),
                SelectFilter::make('severity')->options(DisciplinaryRecord::severityLabels()),
                SelectFilter::make('status')->options([
                    'open'         => 'Open',
                    'under_review' => 'Under Review',
                    'resolved'     => 'Resolved',
                    'appealed'     => 'Appealed',
                ]),
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->defaultSort('incident_date', 'desc')
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDisciplinaryRecords::route('/'),
            'create' => Pages\CreateDisciplinaryRecord::route('/create'),
            'edit'   => Pages\EditDisciplinaryRecord::route('/{record}/edit'),
            'view'   => Pages\ViewDisciplinaryRecord::route('/{record}'),
        ];
    }
}
