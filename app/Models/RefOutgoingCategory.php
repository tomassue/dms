<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

class RefOutgoingCategory extends Model
{
    use SoftDeletes, LogsActivity;
    protected $table = "ref_outgoing_categories";

    protected $fillable = [
        'outgoing_category_name',
        'office_id',
    ];

    // Scope
    protected static function booted()
    {
        static::addGlobalScope('userOffice', function (Builder $builder) {
            $user = Auth::user();

            // Allow Super Admin to see all
            if ($user && $user->hasRole('Super Admin')) {
                return;
            }

            $builder->where(function ($q) use ($user) {
                $q->whereNull('office_id')
                    ->orWhere('office_id', $user->roles()->first()->id);
            });
        });
    }

    // Relationship
    public function office()
    {
        return $this->belongsTo(Role::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ref_outgoing_category')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an outgoing category";
            })
            ->logOnlyDirty();
    }
}
