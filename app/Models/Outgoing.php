<?php

namespace App\Models;

use App\Models\Scopes\DivisionScope;
use App\Models\Scopes\OfficeScope;
use App\Models\Scopes\RoleAndDivisionBasedScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class, DivisionScope::class])]
class Outgoing extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing";
    protected $fillable = [
        'date',
        'details',
        'destination',
        'person_responsible',
        'ref_status_id',
        'outgoingable_type',
        'outgoingable_id',
        'office_id',
        'ref_division_id'
    ];

    // Local Scope
    public function scopeSearch($query, $search)
    {
        return $query->orWhere('id', $search)
            ->where('details', $search)
            ->orWhere('destination', $search)
            ->orWhere('person_responsible', $search);
    }

    public function scopeDateRange($query, $start_date, $end_date)
    {
        return $query->whereBetween('date', [$start_date, $end_date]);
    }

    public function scopeCompleted()
    {
        return $this->status()->where('name', 'completed')->exists();
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        // return $this->date->format('F j, Y');
        return Carbon::parse($this->date)->format('F j, Y');
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
