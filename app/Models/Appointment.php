<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'summary',
        'description',
        'start_time',
        'end_time',
        'google_event_id',
    ];

    protected $dates = ['start_time', 'end_time'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //SCOPE
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now());
    }

    //ACCESOR
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time->format('d/m/Y H:i');
    }


}

