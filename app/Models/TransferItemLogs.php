<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferItemLogs extends Model
{
    use HasFactory;

    protected $table = 'transfer_item_logs'; // Specify the table name if different from default

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source_item_id',       // ID of the item being transferred
        'target_item_id',       // ID of the item receiving the transfer
        'item_name',            // Name of the item
        'transfer_to',          // Name of the target transfer item    
        'transferred_quantity', // Quantity transferred
        'base_price',           // Base price of the source item
        'selling_price',        // Selling price of the source item
        'transferred_by',       // Name of the user who performed the transfer
    ];

    /**
     * Relationship with the source item.
     */
    public function sourceItem()
    {
        return $this->belongsTo(Item::class, 'source_item_id');
    }

    /**
     * Relationship with the target item.
     */
    public function targetItem()
    {
        return $this->belongsTo(Item::class, 'target_item_id');
    }
}
