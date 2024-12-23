<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpiredItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'item_name',
        'category',
        'quantity',
        'date_encoded',
        'expiration_date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the category associated with the damage transaction.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}

