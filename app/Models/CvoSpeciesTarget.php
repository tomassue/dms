<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CvoSpeciesTarget extends Model
{
    use HasFactory, LogsActivity;

    public $table = "cvo_species_targets";
    public $fillable = [
        'cvo_accomplishment_id',
        'ref_species_id',
        'target_value'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cvo_species_target')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
