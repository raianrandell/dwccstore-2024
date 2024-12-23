<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageTransaction extends Model
{
    use HasFactory;

    // Disable updated_at if not using timestamps
    public $timestamps = false;

    protected $fillable = [
        'item_id',
        'category_id',
        'quantity',
        'damage_description',
        'item_name',
    ];
    // Cast 'created_at' to datetime
    protected $casts = [
        'created_at' => 'datetime',
    ];
    /**
     * Get the item associated with the damage transaction.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the category associated with the damage transaction.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // If tracking user
    /*
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    */
}
