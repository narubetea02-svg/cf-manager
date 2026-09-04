<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveStream extends Model
{
    protected $fillable = [
        'shop_id',
        'platform',
        'live_url',
        'status',
        'keyword_filter',
        'price',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function portalSession()
    {
        return $this->hasOne(PortalSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
