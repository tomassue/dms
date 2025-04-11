<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PdfAsset extends Model
{
    protected $table = 'pdf_assets';

    //* Scope: only get assets under header category
    public function scopeHeader(Builder $query)
    {
        $query->where('category', 'header');
    }
}
