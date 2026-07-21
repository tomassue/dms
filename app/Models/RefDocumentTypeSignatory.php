<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefDocumentTypeSignatory extends Model
{
    protected $table = 'ref_document_type_signatories';

    protected $fillable = [
        'ref_document_type_id',
        'name',
        'title',
        'sort_order',
    ];

    public function documentType()
    {
        return $this->belongsTo(RefDocumentType::class, 'ref_document_type_id');
    }
}
