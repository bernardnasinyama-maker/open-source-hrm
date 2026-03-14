<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ============================================================
// MIGRATION 1: Site Expenses
// ============================================================
file_put_contents('database/migrations/2026_03_12_000010_create_site_expenses_table.php', '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create(\'site_expenses\', function (Blueprint $table) {
            $table->id();
            $table->string(\'ref_number\')->unique(); // EXP-2026-001
            $table->string(\'title\');
            $table->enum(\'category\', [\'airtime\',\'data\',\'fuel\',\'petty_cash\',\'accommodation\',\'per_diem\',\'materials\',\'transport\',\'meals\',\'other\']);
            $table->decimal(\'amount\', 12, 2);
            $table->string(\'currency\', 10)->default(\'UGX\');
            $table->date(\'expense_date\');
            $table->unsignedBigInteger(\'employee_id\')->nullable(); // who incurred it
            $table->unsignedBigInteger(\'approved_by\')->nullable();
            $table->enum(\'status\', [\'draft\',\'pending\',\'approved\',\'rejected\'])->default(\'pending\');
            $table->text(\'description\')->nullable();
            $table->string(\'receipt_path\')->nullable();
            $table->text(\'rejection_reason\')->nullable();
            $table->unsignedBigInteger(\'created_by\')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists(\'site_expenses\'); }
};');
echo "Migration 1: site_expenses created\n";

// ============================================================
// MIGRATION 2: Correspondences
// ============================================================
file_put_contents('database/migrations/2026_03_12_000011_create_correspondences_table.php', '<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create(\'correspondences\', function (Blueprint $table) {
            $table->id();
            $table->string(\'ref_number\')->unique(); // RFI-2026-001
            $table->string(\'subject\');
            $table->enum(\'type\', [\'rfi\',\'si\',\'ncr\',\'letter\',\'submittal\',\'mom\',\'drawing\',\'variation\',\'payment_cert\',\'early_warning\',\'other\']);
            $table->enum(\'direction\', [\'incoming\',\'outgoing\']);
            $table->string(\'from_party\');
            $table->string(\'to_party\');
            $table->date(\'date_sent_received\');
            $table->date(\'response_due_date\')->nullable();
            $table->date(\'response_date\')->nullable();
            $table->enum(\'priority\', [\'low\',\'medium\',\'high\',\'critical\'])->default(\'medium\');
            $table->enum(\'status\', [\'open\',\'pending_response\',\'responded\',\'closed\',\'overdue\'])->default(\'open\');
            $table->text(\'description\')->nullable();
            $table->string(\'file_path\')->nullable();
            $table->text(\'notes\')->nullable();
            $table->unsignedBigInteger(\'assigned_to\')->nullable();
            $table->unsignedBigInteger(\'created_by\')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(\'correspondence_followups\', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(\'correspondence_id\');
            $table->text(\'action_taken\');
            $table->date(\'follow_up_date\');
            $table->enum(\'status\', [\'pending\',\'done\',\'escalated\'])->default(\'pending\');
            $table->unsignedBigInteger(\'created_by\')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists(\'correspondence_followups\');
        Schema::dropIfExists(\'correspondences\');
    }
};');
echo "Migration 2: correspondences created\n";

// ============================================================
// MODELS
// ============================================================
file_put_contents('app/Models/SiteExpense.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SiteExpense extends Model {
    use SoftDeletes, LogsActivity;
    protected $fillable = [\'ref_number\',\'title\',\'category\',\'amount\',\'currency\',\'expense_date\',\'employee_id\',\'approved_by\',\'status\',\'description\',\'receipt_path\',\'rejection_reason\',\'created_by\'];
    protected $casts = [\'expense_date\' => \'date\'];
    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName(\'expense\'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function approver() { return $this->belongsTo(Employee::class, \'approved_by\'); }
    public static function nextRef(): string {
        $last = static::withTrashed()->orderByDesc(\'id\')->first();
        $num = $last ? (intval(substr($last->ref_number, -3)) + 1) : 1;
        return \'EXP-\' . date(\'Y\') . \'-\' . str_pad($num, 3, \'0\', STR_PAD_LEFT);
    }
}');
echo "Model: SiteExpense created\n";

file_put_contents('app/Models/Correspondence.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Correspondence extends Model {
    use SoftDeletes, LogsActivity;
    protected $fillable = [\'ref_number\',\'subject\',\'type\',\'direction\',\'from_party\',\'to_party\',\'date_sent_received\',\'response_due_date\',\'response_date\',\'priority\',\'status\',\'description\',\'file_path\',\'notes\',\'assigned_to\',\'created_by\'];
    protected $casts = [\'date_sent_received\' => \'date\', \'response_due_date\' => \'date\', \'response_date\' => \'date\'];
    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName(\'correspondence\'); }
    public function assignee() { return $this->belongsTo(Employee::class, \'assigned_to\'); }
    public function followups() { return $this->hasMany(CorrespondenceFollowup::class); }
    public function isOverdue(): bool { return $this->response_due_date && $this->response_due_date->isPast() && !in_array($this->status, [\'closed\',\'responded\']); }
    public static function nextRef(string $type): string {
        $prefix = strtoupper($type);
        $last = static::withTrashed()->where(\'type\', $type)->orderByDesc(\'id\')->first();
        $num = $last ? (intval(substr($last->ref_number, -3)) + 1) : 1;
        return $prefix . \'-\' . date(\'Y\') . \'-\' . str_pad($num, 3, \'0\', STR_PAD_LEFT);
    }
}');

file_put_contents('app/Models/CorrespondenceFollowup.php', '<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CorrespondenceFollowup extends Model {
    protected $fillable = [\'correspondence_id\',\'action_taken\',\'follow_up_date\',\'status\',\'created_by\'];
    protected $casts = [\'follow_up_date\' => \'date\'];
    public function correspondence() { return $this->belongsTo(Correspondence::class); }
}');
echo "Models: Correspondence + CorrespondenceFollowup created\n";

// ============================================================
// EXPENSE RESOURCE
// ============================================================
mkdir('app/Filament/Resources/Expenses/Pages', 0755, true);

file_put_contents('app/Filament/Resources/Expenses/SiteExpenseResource.php', '<?php
namespace App\Filament\Resources\Expenses;

use App\Models\SiteExpense;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Grid;
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

    public static function form(Form $form): Form {
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
}');

file_put_contents('app/Filament/Resources/Expenses/Pages/ListSiteExpenses.php', '<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListSiteExpenses extends ListRecords {
    protected static string $resource = SiteExpenseResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}');

file_put_contents('app/Filament/Resources/Expenses/Pages/CreateSiteExpense.php', '<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSiteExpense extends CreateRecord {
    protected static string $resource = SiteExpenseResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data["created_by"] = auth()->id();
        return $data;
    }
}');

file_put_contents('app/Filament/Resources/Expenses/Pages/EditSiteExpense.php', '<?php
namespace App\Filament\Resources\Expenses\Pages;
use App\Filament\Resources\Expenses\SiteExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditSiteExpense extends EditRecord {
    protected static string $resource = SiteExpenseResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
    protected function mutateFormDataBeforeSave(array $data): array {
        $data["approved_by"] = in_array($data["status"] ?? "", ["approved","rejected"]) ? auth()->id() : null;
        return $data;
    }
}');
echo "Expense Resource: all pages created\n";

// ============================================================
// CORRESPONDENCE RESOURCE
// ============================================================
mkdir('app/Filament/Resources/Correspondences/Pages', 0755, true);

file_put_contents('app/Filament/Resources/Correspondences/CorrespondenceResource.php', '<?php
namespace App\Filament\Resources\Correspondences;

use App\Models\Correspondence;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

class CorrespondenceResource extends Resource {
    protected static ?string $model = Correspondence::class;
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-envelope-open";
    protected static ?string $navigationLabel = "Correspondence";
    protected static string|\UnitEnum|null $navigationGroup = "Project";
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = "correspondence";

    public static function canViewAny(): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false); }
    public static function canCreate(): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false); }
    public static function canEdit($r): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant"]) ?? false); }
    public static function canDelete($r): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin"]) ?? false); }

    public static function getNavigationBadge(): ?string {
        $overdue = Correspondence::whereNotIn("status",["closed","responded"])
            ->whereNotNull("response_due_date")
            ->where("response_due_date","<", now())
            ->count();
        return $overdue > 0 ? (string)$overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string { return "danger"; }

    public static function form(Form $form): Form {
        return $form->schema([
            Section::make("Document Details")->schema([
                Grid::make(3)->schema([
                    Select::make("type")->label("Type")->required()->live()
                        ->options(["rfi"=>"RFI — Request for Information","si"=>"SI — Site Instruction","ncr"=>"NCR — Non Conformance Report","letter"=>"Letter","submittal"=>"Submittal","mom"=>"MoM — Minutes of Meeting","drawing"=>"Drawing","variation"=>"Variation Order","payment_cert"=>"Payment Certificate","early_warning"=>"Early Warning Notice","other"=>"Other"])
                        ->afterStateUpdated(fn($set,$state) => $set("ref_number", $state ? Correspondence::nextRef($state) : "")),
                    TextInput::make("ref_number")->label("Reference No.")->required()->disabled()->dehydrated(),
                    Select::make("direction")->label("Direction")->required()
                        ->options(["incoming"=>"📥 Incoming","outgoing"=>"📤 Outgoing"]),
                ]),
                Grid::make(2)->schema([
                    TextInput::make("subject")->label("Subject")->required()->columnSpanFull(),
                    TextInput::make("from_party")->label("From")->required()->placeholder("e.g. UNRA / Louis Berger / CRBC"),
                    TextInput::make("to_party")->label("To")->required()->placeholder("e.g. CRBC Uganda"),
                    DatePicker::make("date_sent_received")->label("Date Sent/Received")->required()->default(now()),
                    Select::make("priority")->label("Priority")->options(["low"=>"🟢 Low","medium"=>"🟡 Medium","high"=>"🟠 High","critical"=>"🔴 Critical"])->default("medium"),
                    DatePicker::make("response_due_date")->label("Response Due Date"),
                    DatePicker::make("response_date")->label("Actual Response Date"),
                    Select::make("status")->label("Status")->options(["open"=>"Open","pending_response"=>"Pending Response","responded"=>"Responded","closed"=>"Closed","overdue"=>"Overdue"])->default("open"),
                    Select::make("assigned_to")->label("Assigned To")->options(fn() => Employee::whereNotIn("employee_code",["SYS-001","CRBC-VIEW"])->get()->pluck("name","id"))->searchable(),
                ]),
                Textarea::make("description")->label("Description / Summary")->rows(3)->columnSpanFull(),
                Textarea::make("notes")->label("Internal Notes")->rows(2)->columnSpanFull(),
                FileUpload::make("file_path")->label("Attach Document (PDF/Image)")->directory("correspondences")->acceptedFileTypes(["application/pdf","image/*"])->columnSpanFull(),
            ]),
            Section::make("Follow-up Actions")->schema([
                Repeater::make("followups")->relationship()->schema([
                    Grid::make(3)->schema([
                        Textarea::make("action_taken")->label("Action Taken")->required()->rows(2),
                        DatePicker::make("follow_up_date")->label("Date")->required()->default(now()),
                        Select::make("status")->label("Status")->options(["pending"=>"Pending","done"=>"Done","escalated"=>"Escalated"])->default("pending"),
                    ]),
                ])->addActionLabel("Add Follow-up")->collapsible(),
            ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make("ref_number")->label("Ref")->sortable()->searchable()->badge()->color("primary"),
                TextColumn::make("type")->label("Type")->badge()
                    ->color(fn($s) => match($s){"ncr"=>"danger","si"=>"warning","rfi"=>"info","letter"=>"gray","variation"=>"warning","payment_cert"=>"success",default=>"gray"})
                    ->formatStateUsing(fn($s) => strtoupper($s)),
                TextColumn::make("direction")->label("")->badge()
                    ->formatStateUsing(fn($s) => $s === "incoming" ? "📥 IN" : "📤 OUT")
                    ->color(fn($s) => $s === "incoming" ? "info" : "success"),
                TextColumn::make("subject")->label("Subject")->limit(35)->searchable(),
                TextColumn::make("from_party")->label("From")->limit(20)->searchable(),
                TextColumn::make("date_sent_received")->label("Date")->date("d M Y")->sortable(),
                TextColumn::make("response_due_date")->label("Due")->date("d M Y")->sortable()
                    ->color(fn($r) => $r->response_due_date?->isPast() && !in_array($r->status,["closed","responded"]) ? "danger" : "gray"),
                TextColumn::make("priority")->label("Priority")->badge()
                    ->color(fn($s) => match($s){"critical"=>"danger","high"=>"warning","medium"=>"info","low"=>"success",default=>"gray"}),
                TextColumn::make("status")->label("Status")->badge()
                    ->color(fn($s) => match($s){"closed"=>"success","responded"=>"success","overdue"=>"danger","open"=>"warning","pending_response"=>"info",default=>"gray"}),
                TextColumn::make("followups_count")->label("Follow-ups")->counts("followups")->badge()->color("gray"),
            ])
            ->filters([
                SelectFilter::make("type")->options(["rfi"=>"RFI","si"=>"SI","ncr"=>"NCR","letter"=>"Letter","submittal"=>"Submittal","mom"=>"MoM","drawing"=>"Drawing","variation"=>"Variation","payment_cert"=>"Payment Cert","early_warning"=>"Early Warning"]),
                SelectFilter::make("direction")->options(["incoming"=>"Incoming","outgoing"=>"Outgoing"]),
                SelectFilter::make("status")->options(["open"=>"Open","pending_response"=>"Pending","responded"=>"Responded","closed"=>"Closed","overdue"=>"Overdue"]),
                SelectFilter::make("priority")->options(["critical"=>"Critical","high"=>"High","medium"=>"Medium","low"=>"Low"]),
                Filter::make("overdue")->label("Overdue Only")
                    ->query(fn(Builder $q) => $q->whereNotIn("status",["closed","responded"])->whereNotNull("response_due_date")->where("response_due_date","<",now()))
                    ->toggle(),
            ])
            ->actions([EditAction::make(), ViewAction::make(), DeleteAction::make()])
            ->defaultSort("date_sent_received","desc")
            ->striped();
    }

    public static function getPages(): array {
        return [
            "index"  => Pages\ListCorrespondences::route("/"),
            "create" => Pages\CreateCorrespondence::route("/create"),
            "edit"   => Pages\EditCorrespondence::route("/{record}/edit"),
            "view"   => Pages\ViewCorrespondence::route("/{record}"),
        ];
    }
}');

file_put_contents('app/Filament/Resources/Correspondences/Pages/ListCorrespondences.php', '<?php
namespace App\Filament\Resources\Correspondences\Pages;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListCorrespondences extends ListRecords {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}');

file_put_contents('app/Filament/Resources/Correspondences/Pages/CreateCorrespondence.php', '<?php
namespace App\Filament\Resources\Correspondences\Pages;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use Filament\Resources\Pages\CreateRecord;
class CreateCorrespondence extends CreateRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array {
        $data["created_by"] = auth()->id();
        return $data;
    }
}');

file_put_contents('app/Filament/Resources/Correspondences/Pages/EditCorrespondence.php', '<?php
namespace App\Filament\Resources\Correspondences\Pages;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
class EditCorrespondence extends EditRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [ViewAction::make(), DeleteAction::make()]; }
}');

file_put_contents('app/Filament/Resources/Correspondences/Pages/ViewCorrespondence.php', '<?php
namespace App\Filament\Resources\Correspondences\Pages;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
class ViewCorrespondence extends ViewRecord {
    protected static string $resource = CorrespondenceResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}');
echo "Correspondence Resource: all pages created\n";

// Run migrations
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo "Migrations run\n";

echo "\n=== ALL DONE ===\n";
echo "✅ Site Expenses module — /admin/expenses\n";
echo "✅ Correspondence & Follow-ups — /admin/correspondence\n";
echo "✅ Auto ref numbers: EXP-2026-001, RFI-2026-001 etc.\n";
echo "✅ Overdue badge on sidebar (red count)\n";
echo "✅ Follow-up repeater on each correspondence\n";
echo "\nRun: php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8000\n";
