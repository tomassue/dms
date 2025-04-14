<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class RefIncomingDocumentCategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "ref_incoming_documents_categories";
    protected $fillable = [
        'role_id',
        'name'
    ];

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_document_category')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    //* Relationships
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
