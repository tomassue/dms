<?php

namespace App\Models;

use App\Models\Apo\Accomplishment as ApoAccomplishment;
use App\Models\Scopes\RoleBasedFilterScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class Accomplishment extends Model
{
    use LogsActivity;

    protected $table = 'accomplishments';
    protected $fillable = [
        'ref_accomplishment_category_id',
        'date',
        'details',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];

    //* Accessors
    public function getDateFormattedAttribute()
    {
        return $this->date->format('F j, Y');
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('accomplishment')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    //* Relationships
    public function accomplishment_category()
    {
        return $this->belongsTo(RefAccomplishmentCategory::class, 'ref_accomplishment_category_id', 'id');
    }

    public function apo()
    {
        return $this->hasOne(ApoAccomplishment::class, 'accomplishment_id', 'id');
    }
}
