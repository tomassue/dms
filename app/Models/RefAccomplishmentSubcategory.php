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
class RefAccomplishmentSubcategory extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "ref_accomplishment_sub_categories";

    protected $fillable = [
        'accomplishment_sub_category_name',
        'is_inputtable',
        'ref_accomplishment_category_id',
        'order',
        'parent_id',
        'office_id'
    ];

    //* Global Scope
    # Automatically sort the accomplishment subcategories by order
    protected static function booted()
    {
        static::addGlobalScope('ancient', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }

    //* Scopes
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('category', function ($query) use ($search) {
            $query->where('accomplishment_category_name', 'like', '%' . $search . '%');
        })
            ->orWhere('accomplishment_sub_category_name', 'like', '%' . $search . '%');
    }

    //* Relationships
    public function category()
    {
        return $this->belongsTo(RefAccomplishmentCategory::class, 'ref_accomplishment_category_id', 'id');
    }

    public function office()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }

    public function species()
    {
        return $this->hasMany(RefSpecies::class, 'ref_accomplishment_sub_category_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(RefAccomplishmentSubcategory::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(RefAccomplishmentSubcategory::class, 'parent_id', 'id');
    }

    // Polymorphic relations
    public function targets()
    {
        return $this->morphMany(CvoPeriodTarget::class, 'targetable');
    }

    public function monthlyAccomplishments()
    {
        return $this->morphMany(CvoMonthlyAccomplishment::class, 'accomplishable');
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('accomplishment_subcategory')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} an accomplishment subcategory";
            })
            ->logOnlyDirty();
    }
}
