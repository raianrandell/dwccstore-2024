<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinesHistory extends Model
{
    use HasFactory;

    protected $table = 'fines_history';
    protected $casts = [
        'borrowed_date' => 'datetime',
        'expected_return_date' => 'datetime',
        'actual_return_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    

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
        'borrowed_date', // Add borrowed date
        'expected_return_date', // Add expected return date
        'actual_return_date',
        'condition',
        'cashier_name',
    ];

    

    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'student_id', 'id'); // Ensure the foreign key matches
    }
}
