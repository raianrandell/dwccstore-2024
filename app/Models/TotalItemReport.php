<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalItemReport extends Model
{
    use HasFactory;

    protected $table = 'total_item_report';

    protected $fillable = [
        'item_id',
        'item_name',
        'cat_id',
        'category_name',
        'quantity',
        'unit',
        'base_price',
        'selling_price',
        'total_base_price',
        'total_selling_price',
    ];
}
