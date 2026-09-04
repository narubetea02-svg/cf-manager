<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActionLog extends Model
{
    protected $fillable = [
        'user_id',
        'customer_mapping_id',
        'action',
        'target_type',
        'target_id',
        'before_state',
        'after_state',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
        'meta' => 'array',
    ];

    public function mapping()
    {
        return $this->belongsTo(CustomerMapping::class, 'customer_mapping_id');
    }
}
