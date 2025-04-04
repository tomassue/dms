<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefSignatories extends Model
{
    protected $table = 'ref_signatories';

    protected $fillable = [
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
