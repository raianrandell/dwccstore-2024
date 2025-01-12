<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no',
        'item_name',
        'return_quantity',
        'reason',
        'replacement_item',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_name', 'item_name');
    }

}