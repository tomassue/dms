<?php

namespace App\Models;

use App\Models\Scopes\OfficeScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([OfficeScope::class])]
class CvoAccomplishment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'cvo_accomplishments';
    protected $fillable = [
        'target',
        'office_id',
        'ref_division_id',
    ];

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cvo_accomplishment')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    // Accessor
    public function getFormattedHalfYearPeriodAttribute()
    {
        $halfYearPeriod = $this->target; // Get the value from the database

        if (empty($halfYearPeriod) || !str_contains($halfYearPeriod, '-')) {
            return $halfYearPeriod; // Return as is if not in expected format
        }

        list($year, $half) = explode('-', $halfYearPeriod);

        if ($half === 'H1') {
            return 'January to June ' . $year;
        } elseif ($half === 'H2') {
            return 'July to December ' . $year;
        }

        return $halfYearPeriod; // Fallback if half is not H1 or H2
    }

    public function getAccomplishmentToDateAttribute()
    {
        $halfYearPeriod = $this->getFormattedHalfYearPeriodAttribute();

        // Find the position of the 'to' character
        $position = strrpos($halfYearPeriod, 'to');

        if ($position !== false) {
            // Return the substring after the 'to' character
            return substr($halfYearPeriod, $position + 3);
        }

        return $halfYearPeriod; // Returns the original value if 'to' is not found
    }
}
