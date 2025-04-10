<?php

namespace App\Models;

use App\Models\Apo\Accomplishment as ApoAccomplishment;
use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * * This is a GLOBAL SCOPE
 * I have reasons why we are using global scope since, this model is shared accross different roles (offices), we would want to filter the data based on the user's role and make
 * them visible only to users with the same role.
 * @see https://laravel.com/docs/11.x/eloquent#global-scopes
 */
#[ScopedBy([RoleBasedFilterScope::class])]
class Accomplishment extends Model
{
    use SoftDeletes, LogsActivity;

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
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('F j, Y') : null;
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
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function accomplishment_category()
    {
        return $this->belongsTo(RefAccomplishmentCategory::class, 'ref_accomplishment_category_id', 'id');
    }

    public function apo()
    {
        return $this->hasOne(ApoAccomplishment::class, 'accomplishment_id', 'id');
    }
}
