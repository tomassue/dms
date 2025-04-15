<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class File extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "files";

    protected $fillable = [
        'name',
        'size',
        'type',
        'file',
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('file')
            ->logOnly(['id']) //* We can't log everything because of the file_get_contents()
            ->logOnlyDirty();
    }

    // Polymorphic relationship
    public function fileable()
    {
        return $this->morphTo();
    }
}
