<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Correspondence extends Model {
    use SoftDeletes, LogsActivity;
    protected $fillable = ['ref_number','subject','type','direction','from_party','to_party','date_sent_received','response_due_date','response_date','priority','status','description','file_path','notes','assigned_to','created_by'];
    protected $casts = ['date_sent_received' => 'date', 'response_due_date' => 'date', 'response_date' => 'date'];
    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName('correspondence'); }
    public function assignee() { return $this->belongsTo(Employee::class, 'assigned_to'); }
    public function followups() { return $this->hasMany(CorrespondenceFollowup::class); }
    public function isOverdue(): bool { return $this->response_due_date && $this->response_due_date->isPast() && !in_array($this->status, ['closed','responded']); }
    public static function nextRef(string $type): string {
        $prefix = strtoupper($type);
        $last = static::withTrashed()->where('type', $type)->orderByDesc('id')->first();
        $num = $last ? (intval(substr($last->ref_number, -3)) + 1) : 1;
        return $prefix . '-' . date('Y') . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}