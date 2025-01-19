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
    protected $table = 'borrowers';
    protected $fillable = [
        'student_number',
        'student_name',
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
    
    public function borrowedItems()
    {
        return $this->hasMany(BorrowedItem::class);
    }
    public function finesHistories()
    {
        return $this->hasMany(FinesHistory::class, 'student_id', 'id');
    }
    
}
