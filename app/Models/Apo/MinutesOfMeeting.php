<?php

namespace App\Models\Apo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinutesOfMeeting extends Model
{
    use SoftDeletes;

    protected $table = "apo_minutes_of_meetings";
    protected $fillable = [
        'meeting_id',
        'activity',
        'point_person',
        'expected_output',
        'agreements',
    ];
}
