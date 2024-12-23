<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    // Specify the table if it doesn't follow Laravel's naming convention
    // protected $table = 'sections';

    // Disable updated_at if not using timestamps
    public $timestamps = false;

    // Define which attributes are mass assignable
    protected $fillable = ['sec_name', 'created_at'];

    /**
     * Get the categories for the section.
     */
    public function categories()
    {
        return $this->hasMany(Category::class, 'sec_id');
    }

     /**
     * Get the items for the category.
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'cat_id');
    }
}