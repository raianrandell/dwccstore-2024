<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'item_id', 'item_name', 'quantity', 'price', 'total'
    ];

    /**
     * Define the relationship to Transaction.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Define the relationship to Item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
