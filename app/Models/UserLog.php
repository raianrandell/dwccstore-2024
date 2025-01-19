<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',      // User who performed the activity
        'activity',     // Activity description
        'ip_address',   // IP address
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


