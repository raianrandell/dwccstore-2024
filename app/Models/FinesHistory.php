<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinesHistory extends Model
{
    use HasFactory;

    protected $table = 'fines_history';

    protected $fillable = [
        'student_id',
        'student_name',
        'item_borrowed',
        'quantity',
        'days_late',
        'fines_amount',
        'payment_method',
        'cash_tendered',
        'change',
        'gcash_reference_number',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class, 'student_id', 'student_id');
    }
}
