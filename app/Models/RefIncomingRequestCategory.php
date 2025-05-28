<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class])]
class RefIncomingRequestCategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_incoming_request_categories';

    protected $fillable = [
        'incoming_request_category_name',
        'office_id',
    ];

    //* Activity log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_request_category')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an incoming request category";
            })
            ->logOnlyDirty();
    }

    //* Relationships
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function office()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }
}
