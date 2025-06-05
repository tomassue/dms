<?php

namespace App\Models;

use App\Models\Scopes\DivisionScope;
use App\Models\Scopes\OfficeScope;
use App\Models\Scopes\RoleAndDivisionBasedScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

#[ScopedBy([OfficeScope::class, DivisionScope::class])]
class Outgoing extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "outgoing";
    protected $fillable = [
        'date',
        'details',
        'destination',
        'person_responsible',
        'ref_status_id',
        'outgoingable_type',
        'outgoingable_id',
        'office_id',
        'ref_division_id'
    ];

    // Local Scope
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('id', $search)
                ->orWhere('details', 'like', "%$search%")
                ->orWhere('destination', 'like', "%$search%")
                ->orWhere('person_responsible', 'like', "%$search%");

            $q->orWhereHasMorph(
                'outgoingable',
                [OutgoingOthers::class],
                fn($q) =>
                $q->where('document_name', 'like', "%$search%")
            );

            $q->orWhereHasMorph(
                'outgoingable',
                [OutgoingPayrolls::class],
                fn($q) =>
                $q->where('payroll_type', 'like', "%$search%")
            );

            $q->orWhereHasMorph(
                'outgoingable',
                [OutgoingProcurement::class],
                fn($q) =>
                $q->where('pr_no', 'like', "%$search%")
                    ->orWhere('po_no', 'like', "%$search%")
            );

            $q->orWhereHasMorph(
                'outgoingable',
                [OutgoingRis::class],
                fn($q) =>
                $q->where('document_name', 'like', "%$search%")
                    ->orWhere('ppmp_code', 'like', "%$search%")
            );

            $q->orWhereHasMorph(
                'outgoingable',
                [OutgoingVoucher::class],
                fn($q) =>
                $q->where('voucher_name', 'like', "%$search%")
            );
        });
    }


    public function scopeDateRange($query, $start_date, $end_date)
    {
        return $query->whereBetween('date', [$start_date, $end_date]);
    }

    public function scopeCompleted()
    {
        return $this->status()->where('name', 'completed')->exists();
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        // return $this->date->format('F j, Y');
        return Carbon::parse($this->date)->format('F j, Y');
    }

    // Relationship
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function outgoingable()
    {
        return $this->morphTo();
    }

    public function status()
    {
        return $this->belongsTo(RefStatus::class, 'ref_status_id', 'id');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outgoing')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
