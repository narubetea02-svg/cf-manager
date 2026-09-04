<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CustomerMapping extends Model
{
    public const STATUS_CONNECTED = 'connected';
    public const STATUS_PENDING_MESSENGER = 'pending_messenger';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_EXPIRED = 'expired';

    public const PENDING_WINDOW_MINUTES = 10;

    protected $fillable = [
        'shop_id',
        'portal_session_id',
        'live_stream_id',
        'facebook_page_id',
        'facebook_psid',
        'facebook_name',
        'messenger_ref',
        'messenger_link_pending_at',
        'connected_source',
        'tiktok_username',
        'status',
        'connected_at',
        'last_seen_at',
        'notes',
    ];

    protected $casts = [
        'messenger_link_pending_at' => 'datetime',
        'connected_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function portalSession()
    {
        return $this->belongsTo(PortalSession::class);
    }

    public function orderLinks()
    {
        return $this->hasMany(MessengerOrderLink::class);
    }

    public function activeOrderLinks()
    {
        return $this->hasMany(MessengerOrderLink::class)->where('status', MessengerOrderLink::STATUS_ATTACHED);
    }

    public function replyDrafts()
    {
        return $this->hasMany(MessengerReplyDraft::class);
    }

    public function adminActionLogs()
    {
        return $this->hasMany(AdminActionLog::class)->latest();
    }

    public function effectiveStatus(?Carbon $now = null): string
    {
        $now = $now ?: now();

        if ($this->status === self::STATUS_PENDING_MESSENGER && $this->messenger_link_pending_at) {
            if ($this->messenger_link_pending_at->lt($now->copy()->subMinutes(self::PENDING_WINDOW_MINUTES))) {
                return self::STATUS_EXPIRED;
            }
        }

        return $this->status ?: self::STATUS_CONNECTED;
    }

    public function isPendingExpired(?Carbon $now = null): bool
    {
        return $this->effectiveStatus($now) === self::STATUS_EXPIRED;
    }
}
