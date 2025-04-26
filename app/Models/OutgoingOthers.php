<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OutgoingOthers extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing_others";
    protected $fillable = [
        'document_name'
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
            ->useLogName('outgoing_others')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
