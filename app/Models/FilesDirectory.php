<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilesDirectory extends Model
{   
    protected $table = 'files_directory';

    protected $fillable = [
        'id',
        'data_id',
        'file_category',
        'directory'
    ];
}
