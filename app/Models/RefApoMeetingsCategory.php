<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefApoMeetingsCategory extends Model
{
    use SoftDeletes;

    protected $table = 'ref_apo_meetings_categories';
}
