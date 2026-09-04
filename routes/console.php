<?php

use App\Models\CustomerMapping;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('messenger:expire-pending', function () {
    $affected = CustomerMapping::query()
        ->where('status', CustomerMapping::STATUS_PENDING_MESSENGER)
        ->whereNotNull('messenger_link_pending_at')
        ->where('messenger_link_pending_at', '<', now()->subMinutes(CustomerMapping::PENDING_WINDOW_MINUTES))
        ->update([
            'status' => CustomerMapping::STATUS_EXPIRED,
            'updated_at' => now(),
        ]);

    $this->info("Expired {$affected} pending messenger mapping(s).");
})->purpose('Expire stale pending messenger mappings');
