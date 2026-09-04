<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerReplyDraft extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_DRY_RUN = 'dry_run';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BLOCKED_BY_FLAG = 'blocked_by_flag';
    public const STATUS_BLOCKED_BY_ALLOWLIST = 'blocked_by_allowlist';

    protected $fillable = [
        'customer_mapping_id',
        'shop_id',
        'created_by',
        'facebook_page_id',
        'facebook_psid',
        'draft_text',
        'status',
        'send_enabled',
        'preview_payload',
        'sent_at',
        'response_payload',
        'failure_reason',
    ];

    protected $casts = [
        'send_enabled' => 'boolean',
        'preview_payload' => 'array',
        'response_payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function mapping()
    {
        return $this->belongsTo(CustomerMapping::class, 'customer_mapping_id');
    }
}
