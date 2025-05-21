<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefSignatories extends Model
{
    use SoftDeletes;

    protected $table = 'ref_signatories';

    protected $fillable = [
        'user_id'
    ];

    // Scope
    public function scopeCityAgriculturist($query)
    {
        return $query->whereHas('user.user_metadata', function ($q) {
            $q->where('ref_position_id', 5); // City Agriculturist
        });
    }

    public function scopeWithinDivision($query)
    {
        return $query->whereHas('user.user_metadata', function ($q) {
            $q->where('ref_division_id', auth()->user()->user_metadata->ref_division_id) // Within Division
                ->whereNot('user_id', auth()->user()->id); // Exclude Current User
        });
    }

    // Relatonship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
