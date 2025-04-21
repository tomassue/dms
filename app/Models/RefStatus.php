<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefStatus extends Model
{
    use SoftDeletes;

    protected $table = 'ref_status';
}
