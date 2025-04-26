<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OutgoingRis extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing_ris";
    protected $fillable = [
        'document_name',
        'ppmp_code'
    ];

    // Relationship
    public function outgoing()
    {
        return $this->morphOne(Outgoing::class, 'outgoingable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outgoing_ris')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
