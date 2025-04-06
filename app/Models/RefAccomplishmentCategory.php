<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefAccomplishmentCategory extends Model
{
    use SoftDeletes;

    protected $table = "ref_accomplishment_categories";

    protected $fillable = [
        'name'
    ];
}
