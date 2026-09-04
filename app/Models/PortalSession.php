<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalSession extends Model
{
    protected $fillable = [
        'shop_id',
        'live_stream_id',
        'sid',
        'is_active',
        'connected_count',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    
    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }
}
