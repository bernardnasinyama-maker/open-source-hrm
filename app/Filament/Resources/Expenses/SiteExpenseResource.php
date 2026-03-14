<?php
namespace App\Filament\Resources\Expenses;

use App\Models\SiteExpense;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Builder;

class SiteExpenseResource extends Resource {
    protected static ?string $model = SiteExpense::class;
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-banknotes";
    protected static ?string $navigationLabel = "Site Expenses";
    protected static string|\UnitEnum|null $navigationGroup = "Finance";
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = "expenses";

    public static function canViewAny(): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false); }
    public static function canCreate(): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false); }
    public static function canEdit($r): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false); }
    public static function canDelete($r): bool { return once(fn() => auth()->user()?->hasRole("super_admin") ?? false); }

    public static function form(Schema $form): Schema {
        return $form->schema([
            Section::make("Expense Details")->schema([
                Grid::make(2)->schema([
                    TextInput::make("ref_number")->label("Reference No.")->default(fn() => SiteExpense::nextRef())->disabled()->dehydrated(),
                    DatePicker::make("expense_date")->label("Date")->required()->default(now()),
                    TextInput::make("title")->label("Title / Description")->required()->columnSpanFull(),
                    Select::make("category")->label("Category")->required()->options([
                        "airtime"       => "📱 Airtime",
                        "data"          => "🌐 Data / Internet",
                        "fuel"          => "⛽ Fuel",
                        "petty_cash"    => "💵 Petty Cash",
                        "accommodation" => "🏨 Accommodation",
                        "per_diem"      => "🍽️ Per Diem",
                        "materials"     => "🔧 Site Materials",
                        "transport"     => "🚗 Transport",
                        "meals"         => "🥘 Meals",
                        "other"         => "📦 Other",
                    ]),
                    TextInput::make("amount")->label("Amount (UGX)")->numeric()->required()->prefix("UGX"),
                    Select::make("employee_id")->label("Incurred By")->options(fn() => Employee::whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])->get()->pluck("name","id"))->searchable(),
                    Select::make("status")->label("Status")->options([
                        "draft"    => "Draft",
                        "pending"  => "Pending Approval",
                        "approved" => "Approved",
                        "rejected" => "Rejected",
                    ])->default("pending")->required(),
                    Textarea::make("description")->label("Notes")->columnSpanFull(),
                    Textarea::make("rejection_reason")->label("Rejection Reason")->columnSpanFull()->visible(fn($get) => $get("status") === "rejected"),
                    FileUpload::make("receipt_path")->label("Receipt / Supporting Document")->directory("expenses/receipts")->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make("ref_number")->label("Ref")->sortable()->searchable()->badge()->color("gray"),
                TextColumn::make("expense_date")->label("Date")->date("d M Y")->sortable(),
                TextColumn::make("title")->label("Title")->limit(30)->searchable(),
                TextColumn::make("category")->label("Category")->badge()
                    ->formatStateUsing(fn($s) => match($s) {
                        "airtime"=>"📱 Airtime","data"=>"🌐 Data","fuel"=>"⛽ Fuel",
                        "petty_cash"=>"💵 Petty Cash","accommodation"=>"🏨 Accommodation",
                        "per_diem"=>"🍽️ Per Diem","materials"=>"🔧 Materials",
                        "transport"=>"🚗 Transport","meals"=>"🥘 Meals",default=>"📦 Other"
                    })
                    ->color(fn($s) => match($s) {"fuel"=>"warning","airtime"=>"info","data"=>"info","per_diem"=>"success",default=>"gray"}),
                TextColumn::make("amount")->label("Amount")->money("UGX")->sortable(),
                TextColumn::make("employee.name")->label("By")->default("—"),
                TextColumn::make("status")->label("Status")->badge()
                    ->color(fn($s) => match($s){"approved"=>"success","rejected"=>"danger","pending"=>"warning",default=>"gray"}),
                TextColumn::make("created_at")->label("Added")->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make("category")->options(["airtime"=>"Airtime","data"=>"Data","fuel"=>"Fuel","petty_cash"=>"Petty Cash","accommodation"=>"Accommodation","per_diem"=>"Per Diem","materials"=>"Materials","transport"=>"Transport","meals"=>"Meals","other"=>"Other"]),
                SelectFilter::make("status")->options(["draft"=>"Draft","pending"=>"Pending","approved"=>"Approved","rejected"=>"Rejected"]),
                Filter::make("this_month")->label("This Month")->query(fn(Builder $q) => $q->whereMonth("expense_date", now()->month)->whereYear("expense_date", now()->year))->default(),
            ])
            ->actions([EditAction::make(), ViewAction::make(), DeleteAction::make()])
            ->defaultSort("expense_date","desc")
            ->striped();
    }

    public static function getPages(): array {
        return [
            "index"  => Pages\ListSiteExpenses::route("/"),
            "create" => Pages\CreateSiteExpense::route("/create"),
            "edit"   => Pages\EditSiteExpense::route("/{record}/edit"),
        ];
    }
}