<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemForRent extends Model
{
    use HasFactory;

    protected $table = 'item_for_rent';

    protected $fillable = [
        'item_name',
        'total_quantity',
        'quantity_borrowed',
    ];
}

