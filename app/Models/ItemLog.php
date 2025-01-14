<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 
        'new_item_id',
        'item_name',
        'old_base_price', 
        'new_base_price', 
        'old_selling_price', 
        'new_selling_price', 
        'old_qty_in_stock', 
        'new_qty_in_stock', 
        'old_barcode', 
        'new_barcode',
        'old_expiration_date', 
        'new_expiration_date', 
        'user_id',
        'update_by',
    ];
    
    protected $casts = [
        'old_expiration_date' => 'datetime',
        'new_expiration_date' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id'); 
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

