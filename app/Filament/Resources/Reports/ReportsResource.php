<?php
namespace App\Filament\Resources\Reports;

use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ReportsResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-document-chart-bar";
    protected static ?string $navigationLabel = "Reports";
    protected static string|\UnitEnum|null $navigationGroup = "HR Management";
    protected static ?int $navigationSort = 10;
    protected static ?string $slug = "reports";

    public static function canViewAny(): bool
    {
        return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false);
    }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function table(Table $table): Table {
        $canFull  = auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false;
        $canBasic = auth()->user()?->hasAnyRole(["hr_assistant"]) ?? false;

        return $table
            ->query(
                Employee::query()
                    ->withoutRole(["super_admin","viewer"])
                    ->where("employee_code","!=","SYS-001")
                    ->where("employee_code","!=","CRBC-VIEW")
                    ->with(["department","position"])
            )
            ->columns([
                TextColumn::make("employee_code")->label("Code")->sortable()->searchable(),
                TextColumn::make("name")->label("Employee")->sortable()->searchable(),
                TextColumn::make("department.name")->label("Department")->sortable(),
                TextColumn::make("employment_type")->label("Type")->badge()
                    ->color(fn($s) => match($s){"Permanent"=>"success","Contract"=>"warning","Casual"=>"gray",default=>"gray"}),
                TextColumn::make("hire_date")->label("Hired")->date("d M Y")->sortable(),
                TextColumn::make("is_active")->label("Status")->badge()
                    ->formatStateUsing(fn($s) => $s ? "Active" : "Inactive")
                    ->color(fn($s) => $s ? "success" : "danger"),
                TextColumn::make("basic_salary")->label("Basic Salary (UGX)")->money("UGX")->sortable()->visible($canFull),
            ])
            ->filters([
                SelectFilter::make("department_id")->label("Department")->relationship("department","name"),
                SelectFilter::make("employment_type")->label("Type")->options(["Permanent"=>"Permanent","Contract"=>"Contract","Casual"=>"Casual"]),
            ])
            ->actions([
                Action::make("payslip")
                    ->label("Payslip")
                    ->icon("heroicon-o-document-text")
                    ->color("primary")
                    ->visible($canFull)
                    ->url(fn($record) => route("payslip.employee",["id"=>$record->id]))
                    ->openUrlInNewTab(),
                Action::make("export_emp_row")
                    ->label("Export CSV")
                    ->icon("heroicon-o-arrow-down-tray")
                    ->color("success")
                    ->visible($canBasic || $canFull)
                    ->url(fn($record) => route("employee.export.csv", ["id" => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }

    public static function getPages(): array {
        return ["index" => Pages\ListReports::route("/")];
    }
}