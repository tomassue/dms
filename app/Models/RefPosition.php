<?php

namespace App\Models;

use App\Models\Scopes\NameSortAsc;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([NameSortAsc::class])]
class RefPosition extends Model
{
    use SoftDeletes;

    protected $table = 'ref_positions';

    protected $fillable = [
        'name'
    ];
}
