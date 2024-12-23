<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'services_items';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'transaction_id',
        'service_id',
        'price',
        'service_type',
        'number_of_copies',
        'number_of_hours',
        'amount',
        'total',
    ];

    /**
     * Get the transaction that owns the service item.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the service associated with the service item.
     */
    public function service()
    {
        return $this->belongsTo(Services::class);
    }
}
