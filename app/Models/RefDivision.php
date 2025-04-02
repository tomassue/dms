<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class RefDivision extends Model
{
    use SoftDeletes;

    protected $table = 'ref_divisions';

    protected $fillable = [
        'role_id',
        'name'
    ];

    # Relationship
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
