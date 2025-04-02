<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSignatories extends Model
{
    protected $table = 'ref_signatories';

    protected $fillable = [
        'user_id',
        'ref_position_id',
        'ref_division_id'
    ];
}
