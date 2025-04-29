<?php

namespace App\Models\Apo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes;

    protected $table = 'apo_meetings';
    protected $fillable = [
        'date',
        'description',
        'time_start',
        'time_end',
        'venue',
        'prepared_by',
        'approved_by',
        'noted_by'
    ];
}
