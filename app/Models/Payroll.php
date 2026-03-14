<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
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

    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'pay_date',
        'period',
        'gross_pay',
        'net_pay',
        'deductions',
        'allowances',
        'bonuses',
        'notes',
        'status'
    ];
    protected $casts = [
        'deductions' => 'array',
        'allowances' => 'array',
        'bonuses' => 'array',
    ];

    protected $with = [
        'employee',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


}
