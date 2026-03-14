<?php

$content = <<<'PHP'
<?php
namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Attendance;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class QuickAttendance extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Quick Attendance';
    protected static string|\UnitEnum|null $navigationGroup = 'HR Management';
    protected static ?int $navigationSort = 2;

    public array  $attendance     = [];
    public string $date           = '';
    public string $defaultTimeIn  = '08:00';
    public string $defaultTimeOut = '17:00';

    public function getView(): string
    {
        return 'filament.pages.quick-attendance';
    }

    public function mount(): void
    {
        $this->date = today()->toDateString();
        $employees  = Employee::where('is_active', true)
            ->whereNotIn('employee_code', ['SYS-001'])
            ->with('department')
            ->orderBy('first_name')->get();

        foreach ($employees as $emp) {
            $existing = Attendance::where('employee_id', $emp->id)
                ->whereDate('date', $this->date)->first();
            $this->attendance[$emp->id] = [
                'present'  => $existing ? true : false,
                'time_in'  => $existing?->clock_in  ?? $this->defaultTimeIn,
                'time_out' => $existing?->clock_out ?? $this->defaultTimeOut,
                'name'     => $emp->first_name . ' ' . $emp->last_name,
                'code'     => $emp->employee_code,
                'dept'     => $emp->department?->name ?? 'N/A',
            ];
        }
    }

    public function saveAttendance(): void
    {
        $saved = 0;
        foreach ($this->attendance as $empId => $data) {
            if (!$data['present']) continue;
            $timeIn = $data['time_in'] ?? '08:00';
            $isLate = $timeIn > '08:30';
            Attendance::updateOrCreate(
                ['employee_id' => $empId, 'date' => $this->date],
                [
                    'clock_in'  => $timeIn,
                    'clock_out' => $data['time_out'] ?? '17:00',
                    'is_late'   => $isLate,
                    'remarks'   => $isLate ? 'Late arrival' : null,
                    'status'    => 'present',
                ]
            );
            $saved++;
        }
        Notification::make()
            ->title("Attendance saved for {$saved} employees")
            ->success()->send();
        $this->mount();
    }

    public function markAllPresent(): void
    {
        foreach ($this->attendance as $id => $data) {
            $this->attendance[$id]['present'] = true;
        }
    }

    public function markAllAbsent(): void
    {
        foreach ($this->attendance as $id => $data) {
            $this->attendance[$id]['present'] = false;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Attendance')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('saveAttendance'),
            Action::make('all_present')
                ->label('All Present')
                ->icon('heroicon-o-user-group')
                ->color('info')
                ->action('markAllPresent'),
            Action::make('all_absent')
                ->label('Clear All')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action('markAllAbsent'),
        ];
    }
}
PHP;

file_put_contents('app/Filament/Pages/QuickAttendance.php', $content);
echo "Admin QuickAttendance fixed\n";

$hrContent = str_replace(
    'namespace App\Filament\Pages;',
    'namespace App\Filament\Employee\Pages;',
    $content
);
file_put_contents('app/Filament/Employee/Pages/QuickAttendance.php', $hrContent);
echo "HR QuickAttendance fixed\n";
echo "Done!\n";
