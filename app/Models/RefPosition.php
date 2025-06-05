<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RefPosition extends Model
{
    use LogsActivity;

    protected $table = 'tbl_payroll_ref_employee_position';
    protected $primaryKey = 'position_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Global Scope
    protected static function booted()
    {
        static::addGlobalScope('is_plantilla', function (Builder $builder) {
            $builder->where('is_plantilla', 'Y')
                ->orderBy('position_name', 'asc');
        });
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('position')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} {$eventName} a position.";
            })
            ->logOnlyDirty();
    }
}
