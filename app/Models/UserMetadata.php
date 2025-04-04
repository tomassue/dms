<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMetadata extends Model
{
    protected $table = 'users_metadata';

    protected $fillable = [
        'user_id',
        'ref_division_id',
        'ref_position_id'
    ];

    protected $primaryKey = 'user_id';

    public function division()
    {
        return $this->belongsTo(RefDivision::class, 'ref_division_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(RefPosition::class, 'ref_position_id', 'id');
    }
}
