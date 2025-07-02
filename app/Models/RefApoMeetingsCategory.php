<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RefApoMeetingsCategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_apo_meetings_categories';
    protected $fillable = [
        'name',
    ];

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('apo_meeting_category')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an meeting category name of {$this->name}";
            })
            ->logOnlyDirty();
    }
}
