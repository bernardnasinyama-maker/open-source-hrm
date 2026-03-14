<?php
namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryRecord extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Record {$eventName}");
    }

    protected $fillable = [
        'employee_id', 'raised_by', 'incident_date', 'type', 'severity',
        'subject', 'incident_description', 'action_taken', 'outcome',
        'review_date', 'status', 'employee_acknowledged', 'acknowledged_at',
        'supporting_document', 'notes',
    ];

    protected $casts = [
        'incident_date'        => 'date',
        'review_date'          => 'date',
        'acknowledged_at'      => 'datetime',
        'employee_acknowledged' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'raised_by');
    }

    public static function typeLabels(): array
    {
        return [
            'verbal_warning'   => 'Verbal Warning',
            'written_warning'  => 'Written Warning',
            'final_warning'    => 'Final Warning',
            'suspension'       => 'Suspension',
            'demotion'         => 'Demotion',
            'termination'      => 'Termination',
            'misconduct'       => 'Misconduct',
            'absenteeism'      => 'Absenteeism',
            'insubordination'  => 'Insubordination',
            'other'            => 'Other',
        ];
    }

    public static function severityLabels(): array
    {
        return [
            'minor'    => 'Minor',
            'moderate' => 'Moderate',
            'serious'  => 'Serious',
            'gross'    => 'Gross Misconduct',
        ];
    }
}
