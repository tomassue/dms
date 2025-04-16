<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class RefIncomingRequestCategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_incoming_request_categories';

    protected $fillable = [
        'name'
    ];

    //* Activity log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_request_category')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    //* Relationships
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
