<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'student_name',
        'item_names',
        'item_id',
        'quantity',
        'date_issued',
        'expected_date_returned',
        'actual_date_returned',
    ];

    /**
     * Relationship: Each borrower record belongs to one item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(ItemForRent::class, 'item_id');
    }
}
