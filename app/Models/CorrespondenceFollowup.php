<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CorrespondenceFollowup extends Model {
    protected $fillable = ['correspondence_id','action_taken','follow_up_date','status','created_by'];
    protected $casts = ['follow_up_date' => 'date'];
    public function correspondence() { return $this->belongsTo(Correspondence::class); }
}