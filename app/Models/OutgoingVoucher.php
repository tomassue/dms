<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OutgoingVoucher extends Model
{
    use SoftDeletes, LogsActivity;

    public $table = 'outgoing_vouchers';
    public $fillable = [
        'voucher_name'
    ];

    // Relationship
    public function outgoing()
    {
        return $this->morphOne(Outgoing::class, 'outgoingable');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('outgoing_vouchers')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
