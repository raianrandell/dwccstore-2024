<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_id',
        'barcode',
        'item_name',
        'item_description',
        'item_brand',
        'qtyInStock',
        'low_stock_limit',
        'unit_of_measurement',
        'base_price',
        'selling_price',
        'expiration_date',
        'supplier_info',
        'status',
        'size',
        'color',
        'weight',
    ];

    protected $casts = [
        'selling_price' => 'float',
    ];

    /**
     * Get the category that owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    /**
     * Get all transaction items associated with this item.
     */
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'item_id');
    }

    public function damageTransactions()
    {
        return $this->hasMany(DamageTransaction::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

      public function returnedItems()
    {
        return $this->hasMany(ReturnedItem::class);
    }

    public function itemLogs()
    {
        return $this->hasMany(ItemLog::class, 'item_id');
    }


}
