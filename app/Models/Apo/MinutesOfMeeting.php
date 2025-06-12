<?php

namespace App\Models\Apo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MinutesOfMeeting extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "apo_minutes_of_meetings";
    protected $fillable = [
        'apo_meeting_id',
        'activity',
        'point_person',
        'expected_output',
        'agreements',
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('apo_minutes_of_meetings')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} has {$eventName} a minutes of meeting.";
            })
            ->logOnlyDirty();
    }
}
