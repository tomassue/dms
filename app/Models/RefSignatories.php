<?php

namespace App\Models;

use App\Models\Scopes\DivisionScope;
use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class])]
class RefSignatories extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ref_signatories';

    protected $fillable = [
        'name',
        'title',
        'office_id',
        'ref_division_id'
    ];

    // Scope
    public function scopeCityAgriculturist($query)
    {
        if (!Auth::check() || Auth::user()->hasRole('Super Admin')) {
            return;
        }

        return $query->where('title', 'City Agriculturist');
    }

    public function scopeWithinDivision($query)
    {
        if (!Auth::check() || Auth::user()->hasRole('Super Admin')) {
            return;
        }

        return $query->where('division_id', Auth::user()->user_metadata->division->id);
    }

    public function scopeWithinOffice($query)
    {
        if (!Auth::check() || Auth::user()->hasRole('Super Admin')) {
            return;
        }

        return $query->where('office_id', auth()->user()->roles()->first()->id);
    }

    // Relatonship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function office()
    {
        return $this->belongsTo(Role::class, 'office_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(RefDivision::class, 'ref_division_id', 'id');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('signatories')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} a signatory.";
            })
            ->logOnlyDirty();
    }
}
