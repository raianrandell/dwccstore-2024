<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifiedExpirationDateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'item_name',
        'qty_in_stock',
        'quantity_added',
        'new_expiration_date',
        'modified_by',
    ];

    /**
     * Get the item associated with the log.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user who modified the item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
