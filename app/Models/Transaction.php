<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_no', 'user_id', 'subtotal', 'discount', 'total', 'payment_method', 'cash_tendered', 'change', 'gcash_reference','full_name','id_number','contact_number','department','faculty_name', 'charge_type','status'
    ];

    /**
     * Define the relationship to TransactionItem.
     */

     public function user()
     {
         return $this->belongsTo(User::class);
     }
    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function chargeTransaction()
    {
        return $this->hasOne(ChargeTransaction::class);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

}
