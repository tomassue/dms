<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class])]
class RefAccomplishmentCategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "ref_accomplishment_categories";

    protected $fillable = [
        'accomplishment_category_name',
        'order',
        'office_id'
    ];

    //* Global Scope
    # Automatically sort the accomplishment categories by order
    protected static function booted()
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    //* Relationships
    public function office()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }

    public function sub_category()
    {
        return $this->hasMany(RefAccomplishmentSubcategory::class, 'ref_accomplishment_category_id', 'id');
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('accomplishment_category')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an accomplishment category";
            })
            ->logOnlyDirty();
    }
}
