<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class OutgoingProcurement extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing_procurements";
    protected $fillable = [
        "pr_no",
        "po_no"
    ];

    // Relationship
    public function outgoing()
    {
        return $this->morphOne(Outgoing::class, 'outgoingable');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outgoing_procurements')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
