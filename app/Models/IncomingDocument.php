<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;

class IncomingDocument extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'incoming_documents';
    protected $fillable = [
        'ref_incoming_document_category_id',
        'document_info',
        'date',
        'remarks',
        'file_id'
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
}
