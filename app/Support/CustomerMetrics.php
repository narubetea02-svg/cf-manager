<?php

namespace App\Support;

use App\Models\CustomerMapping;
use Illuminate\Support\Collection;

class CustomerMetrics
{
    public static function realConnectedMessengerUsers(Collection $shopIds, ?Collection $liveStreamIds = null): int
    {
        if ($shopIds->isEmpty()) {
            return 0;
        }

        $query = CustomerMapping::query()
            ->whereIn('shop_id', $shopIds)
            ->where('status', CustomerMapping::STATUS_CONNECTED)
            ->whereNotNull('facebook_psid')
            ->where('facebook_psid', '!=', '');

        if ($liveStreamIds && $liveStreamIds->isNotEmpty()) {
            $query->whereIn('live_stream_id', $liveStreamIds);
        }

        return $query
            ->get(['facebook_page_id', 'facebook_psid'])
            ->filter(fn (CustomerMapping $mapping) => self::isRealPsid($mapping->facebook_psid))
            ->unique(fn (CustomerMapping $mapping) => ($mapping->facebook_page_id ?: 'unknown') . '|' . $mapping->facebook_psid)
            ->count();
    }

    public static function connectedMappingRecords(Collection $shopIds): int
    {
        if ($shopIds->isEmpty()) {
            return 0;
        }

        return CustomerMapping::whereIn('shop_id', $shopIds)
            ->where('status', CustomerMapping::STATUS_CONNECTED)
            ->count();
    }

    public static function realConnectedMessengerUsersByLiveStream(Collection $shopIds): Collection
    {
        if ($shopIds->isEmpty()) {
            return collect();
        }

        return CustomerMapping::query()
            ->whereIn('shop_id', $shopIds)
            ->where('status', CustomerMapping::STATUS_CONNECTED)
            ->whereNotNull('facebook_psid')
            ->where('facebook_psid', '!=', '')
            ->get(['live_stream_id', 'facebook_page_id', 'facebook_psid'])
            ->filter(fn (CustomerMapping $mapping) => self::isRealPsid($mapping->facebook_psid))
            ->groupBy('live_stream_id')
            ->map(function (Collection $mappings) {
                return $mappings
                    ->unique(fn (CustomerMapping $mapping) => ($mapping->facebook_page_id ?: 'unknown') . '|' . $mapping->facebook_psid)
                    ->count();
            });
    }

    public static function isRealPsid(?string $psid): bool
    {
        $value = trim((string) $psid);

        return $value !== ''
            && ctype_digit($value)
            && ! str_starts_with($value, 'manual-ui-')
            && $value !== 'PSID_ADMIN_TEST'
            && ! str_starts_with($value, 'manual-ui-verify-');
    }
}
