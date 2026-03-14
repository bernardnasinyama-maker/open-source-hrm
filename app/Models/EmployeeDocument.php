<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
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
        'employee_id',
        'title',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'expiry_date',
        'notes',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'expiry_date'  => 'date',
        'verified_at'  => 'datetime',
        'is_verified'  => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && $this->expiry_date->isFuture()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public static function typeLabels(): array
    {
        return [
            'contract'             => 'Employment Contract',
            'national_id'          => 'National ID',
            'passport'             => 'Passport',
            'certificate'          => 'Certificate',
            'nssf_card'            => 'NSSF Card',
            'tin_certificate'      => 'TIN Certificate',
            'medical_report'       => 'Medical Report',
            'academic_transcript'  => 'Academic Transcript',
            'reference_letter'     => 'Reference Letter',
            'other'                => 'Other',
        ];
    }
}
