<?php

namespace App\Models\Apo;

use App\Models\IncomingDocument as ModelsIncomingDocument;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class IncomingDocument extends Model
{
    use LogsActivity;

    protected $table = 'apo_incoming_documents';
    protected $fillable = [
        'incoming_document_id',
        'source'
    ];

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('apo_incoming_document')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    //* Relationship
    public function incomingDocument()
    {
        return $this->belongsTo(ModelsIncomingDocument::class, 'incoming_document_id', 'id');
    }
}
