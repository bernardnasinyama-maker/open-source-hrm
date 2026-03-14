<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SiteExpense extends Model {
    use SoftDeletes, LogsActivity;
    protected $fillable = ['ref_number','title','category','amount','currency','expense_date','employee_id','approved_by','status','description','receipt_path','rejection_reason','created_by'];
    protected $casts = ['expense_date' => 'date'];
    public function getActivitylogOptions(): LogOptions { return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName('expense'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function approver() { return $this->belongsTo(Employee::class, 'approved_by'); }
    public static function nextRef(): string {
        $last = static::withTrashed()->orderByDesc('id')->first();
        $num = $last ? (intval(substr($last->ref_number, -3)) + 1) : 1;
        return 'EXP-' . date('Y') . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}