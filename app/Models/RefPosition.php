<?php

namespace App\Models;

use App\Models\Scopes\NameSortAsc;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([NameSortAsc::class])]
class RefPosition extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_positions';

    protected $fillable = [
        'name'
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('position')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} a position.";
            })
            ->logOnlyDirty();
    }
}
