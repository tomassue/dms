<?php

namespace App\Models;

use App\Models\Scopes\RoleAndDivisionBasedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleAndDivisionBasedScope::class])]
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
    protected $casts = [
        'date' => 'date',
    ];

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    // Relationship
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function outgoingable()
    {
        return $this->morphTo();
    }

    public function status()
    {
        return $this->belongsTo(RefStatus::class, 'ref_status_id', 'id');
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
