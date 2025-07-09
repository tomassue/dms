<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CvoSpeciesMonthlyAccomplishment extends Model
{
    use LogsActivity;

    protected $table = 'cvo_species_monthly_accomplishments';
    protected $fillable = [
        'cvo_accomplishment_id',
        'ref_species_id',
        'target_value'
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cvo_species_monthly_accomplishments')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
