<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvoMonthlyAccomplishment extends Model
{
    use HasFactory;

    protected $table = 'cvo_monthly_accomplishments';

    protected $fillable = [
        'cvo_accomplishment_id',
        'accomplishable_type',
        'accomplishable_id',
        'month',
        'accomplished_value',
        'remarks',
        'office_id',
        'ref_division_id',
        'user_id',
    ];

    // Relationship
    public function cvoAccomplishment()
    {
        return $this->belongsTo(CvoAccomplishment::class);
    }

    public function accomplishable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
