<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class RefStatus extends Model
{
    use SoftDeletes;

    protected $table = 'ref_status';

    //Scope
    public function scopeOutgoing($query)
    {
        return $query->whereNotIn('name', ['forwarded', 'cancelled', 'received']);
    }

    public function scopeIncoming($query)
    {
        $user = Auth::user();
        $isCVO = $user->hasRole('CITY VETERINARY OFFICE');

        if ($isCVO) {
            return $query->whereNotIn('name', ['forwarded', 'cancelled', 'received']);
        }
        // APO / Default
        return $query->whereNotIn('name', ['forwarded', 'received', 'pending', 'processed', 'completed']);
    }
}
