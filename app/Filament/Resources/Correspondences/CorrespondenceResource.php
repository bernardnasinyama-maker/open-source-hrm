<?php
namespace App\Filament\Resources\Correspondences;

use App\Models\Correspondence;
use App\Models\Employee;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

class CorrespondenceResource extends Resource {
    protected static ?string $model = Correspondence::class;
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-envelope-open";
    protected static ?string $navigationLabel = "Correspondence";
    protected static string|\UnitEnum|null $navigationGroup = "Project";
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = "correspondence";

    public static function canViewAny(): bool { return once(fn() => auth()->user()?->hasAnyRole(["super_admin","admin","hr_assistant","viewer"]) ?? false); }
    public static function canCreate(): bool { return once(fn() => auth()->user()?->hasRole("super_admin") ?? false); }
    public static function canEdit($r): bool { return once(fn() => auth()->user()?->hasRole("super_admin") ?? false); }
    public static function canDelete($r): bool { return once(fn() => auth()->user()?->hasRole("super_admin") ?? false); }

    public static function getNavigationBadge(): ?string {
        $overdue = Correspondence::whereNotIn("status",["closed","responded"])
            ->whereNotNull("response_due_date")
            ->where("response_due_date","<", now())
            ->count();
        return $overdue > 0 ? (string)$overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string { return "danger"; }

    public static function form(Schema $form): Schema {
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
                    ->color(fn($state, $record) => $record?->response_due_date?->isPast() && !in_array($record?->status,["closed","responded"]) ? "danger" : "gray"),
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
}