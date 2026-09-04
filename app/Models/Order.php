<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'shop_id',
        'product_id',
        'customer_name',
        'customer_phone',
        'customer_username',
        'code',
        'quantity',
        'total_price',
        'status',
        'slip_image',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function messengerOrderLinks()
    {
        return $this->hasMany(MessengerOrderLink::class);
    }
}
