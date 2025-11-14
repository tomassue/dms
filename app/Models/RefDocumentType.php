<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class RefDocumentType extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_document_type';

    protected $fillable = [
        'document_name',
        'office_id',
    ];

    //* Activity log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('document_type')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an document type";
            })
            ->logOnlyDirty();
    }

    //* Relationships
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function office()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }
}
