<?php

namespace App\Models\Apo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Accomplishment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "apo_accomplishments";
    protected $fillable = [
        'accomplishment_id',
        'start_date',
        'end_date',
        'next_steps',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    //* Accessors
    public function getStartDateFormattedAttribute()
    {
        return $this->start_date->format('F j, Y');
    }

    public function getEndDateFormattedAttribute()
    {
        return $this->end_date->format('F j, Y');
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('apo_accomplishment')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
