<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinesHistory extends Model
{
    use HasFactory;

    protected $table = 'fines_history';

    // In FinesHistory Model
    protected $fillable = [
        'student_id',
        'student_name',
        'item_borrowed',
        'quantity',
        'days_late',
        'late_fee',  // Add late fee
        'additional_fee', // Add additional fee for damage/lost items
        'fines_amount',
        'payment_method',
        'cash_tendered',
        'change',
        'gcash_reference_number',
        'actual_return_date',
        'condition', 
    ];

    

    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'student_id', 'id'); // Ensure the foreign key matches
    }
}
