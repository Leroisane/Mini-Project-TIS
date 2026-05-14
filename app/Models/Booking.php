<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
    'booking_id', 'event_id', 'qty', 'total_price', 'status', 'va_number'
];
}
