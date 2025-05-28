<?php

namespace App\Models;

use App\Models\Scopes\IsForwardedFilterScope;
use App\Models\Scopes\OfficeScope;
use App\Models\Scopes\RoleBasedFilterScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy([OfficeScope::class, IsForwardedFilterScope::class])]
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
        'remarks',
        'office_id'
    ];

    // Accessors
    public function getFormattedDateRequestedAttribute()
    {
        return $this->date_requested ? Carbon::parse($this->date_requested)->format('M d, Y') : null;
    }

    public function getFormattedDateTimeAttribute()
    {
        return $this->date_time ? Carbon::parse($this->date_time)->format('M d, Y h:i A') : null;
    }

    // Scopes
    public function scopeIsForwarded()
    {
        return $this->forwards()->exists();
    }

    public function scopeIsCompleted()
    {
        return $this->status()->where('name', 'completed')->exists(); // returns true or false
    }

    public function scopeIsCancelled()
    {
        return $this->status()->where('name', 'cancelled')->exists();
    }

    public function scopePending($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where('name', 'pending');
        });
    }

    public function scopeCompleted($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where('name', 'completed');
        });
    }

    public function scopeForwarded($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where('name', 'forwarded');
        });
    }

    /**
     * scopeReceived
     * For ADMIN ONLY
     */
    public function scopeReceived($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where('name', 'received');
        });
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
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} has {$eventName} an incoming request with a reference number of {$this->no}";
            })
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

    public function forwards()
    {
        return $this->morphMany(Forwarded::class, 'forwardable');
    }
}
