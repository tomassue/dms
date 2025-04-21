<?php

namespace App\Models;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;

#[ScopedBy([RoleBasedFilterScope::class])]
class IncomingDocument extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'incoming_documents';
    protected $fillable = [
        'ref_incoming_document_category_id',
        'document_info',
        'date',
        'remarks'
    ];

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_document')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    //* Relationship
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function category()
    {
        return $this->belongsTo(RefIncomingDocumentCategory::class, 'ref_incoming_document_category_id', 'id');
    }

    public function apoDocument()
    {
        return $this->hasOne(ApoIncomingDocument::class, 'incoming_document_id', 'id');
    }
}
