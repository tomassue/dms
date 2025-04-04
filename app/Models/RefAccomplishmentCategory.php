<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefAccomplishmentCategory extends Model
{
    protected $table = "ref_accomplishment_categories";

    protected $fillable = [
        'name'
    ];
}
