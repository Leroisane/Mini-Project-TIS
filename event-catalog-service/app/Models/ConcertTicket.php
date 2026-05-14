<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcertTicket extends Model
{
    // Beritahu Laravel nama tabel aslinya di Supabase
    protected $table = 'concert_tickets';
    
    // Matikan timestamps jika kamu tidak punya kolom updated_at
    public $timestamps = false;

    /**
     * Karena kita menggunakan UUID di PostgreSQL Supabase:
     */

    // 1. Beritahu Laravel bahwa primary key-nya bukan integer auto-increment
    public $incrementing = false;

    // 2. Beritahu Laravel bahwa tipe data primary key-nya adalah string
    protected $keyType = 'string';
}