<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class])]
class RefDivision extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_divisions';

    protected $fillable = [
        'office_id',
        'name'
    ];

    # Relationship
    public function roles()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }

    # Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ref_division')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "A reference division has been {$eventName} by {$userName}";
            })
            ->logOnlyDirty();
    }
}
