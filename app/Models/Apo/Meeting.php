<?php

namespace App\Models\Apo;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Meeting extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'apo_meetings';
    protected $fillable = [
        'date',
        'description',
        'time_start',
        'time_end',
        'venue',
        'prepared_by',
        'approved_by',
        'noted_by'
    ];

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date ? Carbon::parse($this->date)->format('F d, Y') : null;
    }

    public function getFormattedTimeStartAttribute()
    {
        return $this->time_start ? Carbon::parse($this->time_start)->format('h:i A') : null;
    }

    public function getFormattedTimeEndAttribute()
    {
        return $this->time_end ? Carbon::parse($this->time_end)->format('h:i A') : null;
    }

    public function getTimeRangeAttribute()
    {
        return $this->formatted_time_end ? $this->formatted_time_start . ' - ' . $this->formatted_time_end : $this->formatted_time_start;
    }

    // Relationship
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function notedBy()
    {
        return $this->belongsTo(User::class, 'noted_by', 'id');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('meeting')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
