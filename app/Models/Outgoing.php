<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class Outgoing extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing";
    protected $fillable = [
        'date',
        'details',
        'destination',
        'person_responsible',
        'ref_status_id'
    ];

    // Relationship
    public function outgoingable()
    {
        return $this->morphTo();
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outgoing')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
