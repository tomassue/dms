<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvoPeriodTarget extends Model
{
    use HasFactory;

    protected $table = 'cvo_period_targets';

    protected $fillable = [
        'cvo_accomplishment_id',
        'targetable_type',
        'targetable_id',
        'target_value'
    ];

    public function cvoAccomplishment()
    {
        return $this->belongsTo(CvoAccomplishment::class);
    }

    public function targetable()
    {
        return $this->morphTo();
    }
}
