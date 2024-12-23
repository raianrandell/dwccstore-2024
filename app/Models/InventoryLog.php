<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    // Define fillable fields
    protected $fillable = ['message', 'type', 'user_id', 'manage_by'];

    // Define the relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

