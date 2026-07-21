<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefIncomingRequestCategorySignatory extends Model
{
    protected $table = 'ref_incoming_request_category_signatories';

    protected $fillable = [
        'ref_incoming_request_category_id',
        'name',
        'title',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(RefIncomingRequestCategory::class, 'ref_incoming_request_category_id');
    }
}
