<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefPosition extends Model
{
    use SoftDeletes;

    protected $table = 'ref_positions';

    protected $fillable = [
        'name'
    ];
}
