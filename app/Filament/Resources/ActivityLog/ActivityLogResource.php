<?php
namespace App\Filament\Resources\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource {
    protected static ?string $model = Activity::class;
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-clipboard-document-list";
    protected static ?string $navigationLabel = "Audit Trail";
    protected static string|\UnitEnum|null $navigationGroup = "HR Management";
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = "audit-trail";

    public static function canViewAny(): bool
    {
        return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false);
    }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool {
        return auth()->user()?->hasRole("super_admin") ?? false;
    }

    public static function table(Table $table): Table {
        return $table
            ->query(Activity::query()->latest()->with("causer"))
            ->columns([
                TextColumn::make("id")->label("#")->sortable(),
                TextColumn::make("causer.name")->label("User")->default("System")->badge()->color("primary")->searchable(),
                TextColumn::make("event")->label("Action")->badge()
                    ->color(fn($state) => match($state){"created"=>"success","updated"=>"warning","deleted"=>"danger",default=>"gray"})
                    ->formatStateUsing(fn($state) => strtoupper($state ?? "system")),
                TextColumn::make("log_name")->label("Module")->badge()->color("info")
                    ->formatStateUsing(fn($state) => ucwords(str_replace("_"," ",$state))),
                TextColumn::make("subject_type")->label("Record")
                    ->formatStateUsing(fn($state) => $state ? class_basename($state) : "N/A"),
                TextColumn::make("subject_id")->label("ID")->default("—"),
                TextColumn::make("description")->label("Description")->limit(50)->searchable(),
                TextColumn::make("created_at")->label("When")->since()->sortable()
                    ->tooltip(fn($record) => $record->created_at->format("d M Y H:i:s")),
            ])
            ->filters([
                SelectFilter::make("event")->label("Action")
                    ->options(["created"=>"Created","updated"=>"Updated","deleted"=>"Deleted"]),
                SelectFilter::make("log_name")->label("Module")
                    ->options(["employee"=>"Employee","payroll"=>"Payroll","leave"=>"Leave","attendance"=>"Attendance","document"=>"Document","disciplinary"=>"Disciplinary"]),
                Filter::make("date_from")->label("From Date")
                    ->form([DatePicker::make("date_from")->label("From")])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data["date_from"] ?? null,
                            fn($q, $date) => $q->whereDate("created_at", ">=", $date)
                        );
                    }),
                Filter::make("date_until")->label("Until Date")
                    ->form([DatePicker::make("date_until")->label("Until")])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data["date_until"] ?? null,
                            fn($q, $date) => $q->whereDate("created_at", "<=", $date)
                        );
                    }),
            ])
            ->defaultSort("created_at","desc")
            ->striped()
            ->paginated([25,50,100]);
    }

    public static function getPages(): array {
        return ["index" => Pages\ListActivityLog::route("/")];
    }
}