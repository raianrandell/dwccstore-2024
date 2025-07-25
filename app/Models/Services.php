<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'status',
    ];

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }
}

