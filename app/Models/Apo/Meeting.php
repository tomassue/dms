<?php

namespace App\Models\Apo;

use App\Models\RefApoMeetingsCategory;
use App\Models\RefSignatories;
use App\Models\Scopes\RoleAndDivisionBasedScope;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleAndDivisionBasedScope::class])]
class Meeting extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'apo_meetings';
    protected $fillable = [
        'date',
        'ref_apo_meetings_category_id',
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

    // Scope
    public function scopeDateRange($query, $start_date, $end_date)
    {
        if (!$start_date && !$end_date) {
            return $query;
        }

        return $query->whereBetween('date', [$start_date, $end_date]);
    }

    // Relationship
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(RefSignatories::class, 'approved_by', 'id');
    }

    public function notedBy()
    {
        return $this->belongsTo(RefSignatories::class, 'noted_by', 'id');
    }

    public function apoMeetingsCategory()
    {
        return $this->belongsTo(RefApoMeetingsCategory::class, 'ref_apo_meetings_category_id', 'id');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('meeting')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} has {$eventName} a meeting held on {$this->date}.";
            })
            ->logOnlyDirty();
    }
}
