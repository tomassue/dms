<?php

namespace App\Models;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\Scopes\IsForwardedFilterScope;
use App\Models\Scopes\OfficeScope;
use App\Models\Scopes\RoleBasedFilterScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

#[ScopedBy([OfficeScope::class, IsForwardedFilterScope::class])]
class IncomingDocument extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'incoming_documents';
    protected $fillable = [
        'ref_incoming_document_category_id',
        'document_info',
        'date',
        'ref_status_id',
        'remarks',
        'office_id'
    ];

    //* Scopes
    public function scopeIsForwarded()
    {
        return $this->forwards()->exists();
    }

    public function scopeIsCompleted()
    {
        return $this->status()->where('name', 'completed')->exists();
    }

    public function scopeIsCancelled()
    {
        return $this->status()->where('name', 'cancelled')->exists();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('document_info', 'like', '%' . $search . '%')
                ->orWhereHas('apoDocument', function ($q) use ($search) {
                    $q->where('source', 'like', '%' . $search . '%');
                })
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('incoming_document_category_name', 'like', '%' . $search . '%');
                });
        });
    }

    public function scopeForwarded($query)
    {
        return $query->whereHas('status', function ($query) {
            $query->where('name', 'forwarded');
        });
    }

    public function scopeDateRangeFilter($query, $start_date, $end_date)
    {
        return $query->whereBetween('date', [$start_date, $end_date]);
    }

    /**
     * scopeReceived
     * For ADMIN ONLY
     */
    public function scopeReceived($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', 'received'));
    }

    //* Mutators
    public function getFormattedDateAttribute()
    {
        return $this->date ? Carbon::parse($this->date)->format('M d, Y') : null;
    }

    //* Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incoming_document')
            ->logOnly(['*'])
            ->setDescriptionForEvent(function (string $eventName) {
                $user = Auth::user();
                $userName = $user ? $user->name : 'System';

                return "{$userName} has {$eventName} an incoming document with a document info of {$this->document_info}";
            })
            ->logOnlyDirty();
    }

    //* Relationship
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function category()
    {
        return $this->belongsTo(RefIncomingDocumentCategory::class, 'ref_incoming_document_category_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(RefStatus::class, 'ref_status_id', 'id');
    }

    public function apoDocument()
    {
        return $this->hasOne(ApoIncomingDocument::class, 'incoming_document_id', 'id');
    }

    public function forwards()
    {
        return $this->morphMany(Forwarded::class, 'forwardable');
    }
}
