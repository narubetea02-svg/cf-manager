<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class IntegrationsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shops = Shop::with('messengerSetting')->where('user_id', $user->id)->get();
        $shop = $shops->first();

        $messengerConfigured = $shops->contains(
            fn (Shop $s) => $s->messengerSetting?->is_active && filled($s->messengerSetting?->fb_page_id)
        );
        $messengerHasToken = $shops->contains(
            fn (Shop $s) => filled($s->messengerSetting?->fb_page_token)
        );

        $tiktokConfigured = (bool) (config('tiktok.enabled') && filled(config('tiktok.api_key')));
        $tiktokUsername = $shop?->tiktok_username;
        $tiktokStatus = (array) (($shop?->settings ?? [])['tiktok'] ?? []);

        $lineConfigured = (bool) (config('line.enabled') && filled(config('line.channel_token')));

        $webhookEnabled = (bool) config('facebook.webhook_enabled', false);

        return view('integrations.index', compact(
            'shops',
            'shop',
            'messengerConfigured',
            'messengerHasToken',
            'tiktokConfigured',
            'tiktokUsername',
            'tiktokStatus',
            'lineConfigured',
            'webhookEnabled',
        ));
    }
}
