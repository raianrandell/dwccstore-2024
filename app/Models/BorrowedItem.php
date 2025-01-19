<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_id',
        'borrowed_date',
        'return_date',
        'actual_return_date',
        'status',
        'item_id',
        'condition',
    ];
 
    public function item()
    {
        return $this->belongsTo(ItemForRent::class, 'item_id');
    }


    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'borrower_id');
    }

}