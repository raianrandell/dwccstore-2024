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

       public function borrowers()
     {
         return $this->hasMany(Borrower::class, 'item_id');
     }

     public function getDamagedQuantityAttribute()
     {
         return FinesHistory::where('item_borrowed', $this->item_name)
                            ->where('condition', 'Damaged')
                            ->count();
     }
 
     // Accessor for lost quantity
     public function getLostQuantityAttribute()
     {
         return FinesHistory::where('item_borrowed', $this->item_name)
                            ->where('condition', 'Lost')
                            ->count();
     }
}

