<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RefSpecies extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "ref_species";
    protected $fillable = [
        'species_name',
        'ref_accomplishment_sub_category_id',
        'office_id'
    ];

    //* Relationships
    public function accomplishmentSubcategory()
    {
        return $this->belongsTo(RefAccomplishmentSubcategory::class, 'ref_accomplishment_sub_category_id', 'id');
    }

    // Polymorphic relations
    public function targets()
    {
        return $this->morphMany(CvoPeriodTarget::class, 'targetable');
    }

    public function monthlyAccomplishments()
    {
        return $this->morphMany(CvoMonthlyAccomplishment::class, 'accomplishable');
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('species')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} a species name of {$this->species_name}";
            })
            ->logOnlyDirty();
    }
}
