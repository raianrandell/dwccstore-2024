<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Specify the table if it doesn't follow Laravel's naming convention
    // protected $table = 'categories';

    // Disable updated_at if not using timestamps
    public $timestamps = false;

    // Define which attributes are mass assignable
    protected $fillable = ['sec_id', 'category_name', 'stock_no', 'created_at'];

    /**
     * Get the section that owns the category.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'sec_id');
    }
    
    public function items()
    {
        return $this->hasMany(Item::class, 'cat_id');
    }

    public function damageTransactions()
    {
        return $this->hasMany(DamageTransaction::class);
    }


}
