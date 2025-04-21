<?php

namespace App\Models;

use App\Models\Scopes\RoleBasedFilterScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([RoleBasedFilterScope::class])]
class IncomingRequest extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'incoming_requests';

    protected $fillable = [
        'no',
        'office_barangay_organization',
        'date_requested',
        'ref_incoming_request_category_id',
        'date_time',
        'contact_person_name',
        'contact_person_number',
        'description',
        'ref_status_id',
        'remarks'
    ];

    protected $casts = [
        'date_requested' => 'date'
    ];

    // Accessors
    public function getFormattedDateRequestedAttribute()
    {
        return $this->date_requested ? $this->date_requested->format('F j, Y') : null;
    }

    // Generate Unique Reference No.
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Check if the reference number is already set
            if (empty($model->no)) {
                $model->no = self::generateUniqueReference('REF-', 8);
            }
        });
    }

    public static function generateUniqueReference(string $prefix = '', int $length = 6): string
    {
        do {
            // Generate the reference number with the specified prefix
            $reference = $prefix . strtoupper(substr(uniqid(), -$length));
        } while (self::where('no', $reference)->exists());

        return $reference;
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_request')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    // Relationship
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function category()
    {
        return $this->belongsTo(RefIncomingRequestCategory::class, 'ref_incoming_request_category_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(RefStatus::class, 'ref_status_id', 'id');
    }
}
