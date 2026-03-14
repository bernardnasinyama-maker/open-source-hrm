<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table = "tasks";

    protected $fillable = [
        "title","description","status","sort_order",
        "assignee_id","due_date","position",
        "priority","board_status","board_position",
        "taskable_type","taskable_id","user_id",
    ];

    protected $casts = [
        "due_date"       => "datetime",
        "sort_order"     => "integer",
        "assignee_id"    => "integer",
        "position"       => "integer",
        "board_position" => "integer",
    ];

    protected $appends = ["date","email"];

    public function taskable()  { return $this->morphTo(); }
    public function assignee()  { return $this->belongsTo(Employee::class, "assignee_id"); }
    public function creator()   { return $this->belongsTo(Employee::class, "user_id"); }

    public function getDateAttribute()  { return $this->due_date?->format("d M Y"); }
    public function getEmailAttribute() { return $this->assignee?->email; }
}