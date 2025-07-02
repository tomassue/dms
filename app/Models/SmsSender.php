<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSender extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $table = 'tbl_sms_for_sending';

    public $timestamps = false;

    protected $fillable = [
        'trans_id',
        'received_id',
        'recipient',
        'recipient_message',
        'send_date',
    ];
}
