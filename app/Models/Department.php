<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
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

    //
    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id',
    ];
    // protected $with = ['employees'];
    protected $casts = [
        'manager_id' => 'integer',
    ];

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
