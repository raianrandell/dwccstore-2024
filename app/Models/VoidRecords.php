<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidRecords extends Model
{
    use HasFactory;

    // Disable updated_at if not using timestamps
    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'price',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'voided_at' => 'datetime', // Cast to Carbon instance
    ];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_name', 'item_name');
    }
}

