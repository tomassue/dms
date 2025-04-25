<?php

namespace App\Models;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\Scopes\IsForwardedFilterScope;
use App\Models\Scopes\RoleBasedFilterScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;

#[ScopedBy([RoleBasedFilterScope::class, IsForwardedFilterScope::class])]
class IncomingDocument extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'incoming_documents';
    protected $fillable = [
        'ref_incoming_document_category_id',
        'document_info',
        'date',
        'ref_status_id',
        'remarks'
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
                });
        });
    }

    public function scopeDateRangeFilter($query, $start_date, $end_date)
    {
        return $query->whereBetween('date', [$start_date, $end_date]);
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
