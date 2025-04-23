<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class Forwarded extends Model
{
    use LogsActivity;

    protected $table = 'forwarded';
    protected $fillable = [
        'ref_division_id'
    ];

    // Scope
    public function scopeRequests()
    {
        // return forwarded incoming requests
        return $this->where('forwardable_type', IncomingRequest::class);
    }

    public function scopeDocuments()
    {
        // return forwarded incoming documents
        return $this->where('forwardable_type', IncomingDocument::class);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('forwarded')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    // Relationship
    public function forwardable()
    {
        return $this->morphTo();
    }

    public function division()
    {
        return $this->belongsTo(RefDivision::class, 'ref_division_id', 'id');
    }
}
