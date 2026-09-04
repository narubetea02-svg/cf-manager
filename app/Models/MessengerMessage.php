<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessengerMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'page_id',
        'psid',
        'sender_name',
        'message_text',
        'direction',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
