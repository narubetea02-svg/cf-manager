<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerOrderLink extends Model
{
    public const STATUS_ATTACHED = 'attached';
    public const STATUS_DETACHED = 'detached';
    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $fillable = [
        'customer_mapping_id',
        'order_id',
        'status',
        'matched_by',
        'confidence',
        'attached_by',
        'detached_at',
        'notes',
    ];

    protected $casts = [
        'detached_at' => 'datetime',
    ];

    public function mapping()
    {
        return $this->belongsTo(CustomerMapping::class, 'customer_mapping_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'attached_by');
    }
}
