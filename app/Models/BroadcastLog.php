<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastLog extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'order_ids',
        'recipient_count',
        'channel',
        'status',
        'detail',
    ];

    protected $casts = [
        'order_ids' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
