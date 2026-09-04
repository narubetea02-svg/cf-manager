<?php

namespace App\Services;

use App\Models\CustomerMapping;
use App\Models\MessengerReplyDraft;
use App\Models\Order;
use App\Models\Shop;

class AutoMessageService
{
    public function __construct(
        protected MessengerSendService $messengerSendService
    ) {
    }

    public function renderTemplate(Shop $shop, string $key, array $vars = []): ?string
    {
        $auto = (array) (($shop->settings ?? [])['auto_message'] ?? []);
        if (empty($auto['enabled'])) {
            return null;
        }

        $template = trim((string) ($auto[$key] ?? ''));
        if ($template === '') {
            return null;
        }

        $replacements = [
            '{shop_name}' => $vars['shop_name'] ?? $shop->name,
            '{customer_name}' => $vars['customer_name'] ?? ($vars['customer_username'] ?? 'ลูกค้า'),
            '{order_no}' => $vars['order_no'] ?? '',
            '{total_amount}' => $vars['total_amount'] ?? '',
            '{tracking_no}' => $vars['tracking_no'] ?? '',
            '{shipping_method}' => $vars['shipping_method'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function queueForOrderEvent(Order $order, string $templateKey, array $extra = []): ?MessengerReplyDraft
    {
        $shop = $order->shop;
        if (! $shop) {
            return null;
        }

        $text = $this->renderTemplate($shop, $templateKey, array_merge([
            'customer_name' => $order->customer_name ?: $order->customer_username,
            'customer_username' => $order->customer_username,
            'order_no' => (string) $order->id,
            'total_amount' => number_format((float) $order->total_price, 2),
            'tracking_no' => $order->tracking_number ?? '',
        ], $extra));

        if ($text === null) {
            return null;
        }

        $mapping = CustomerMapping::where('shop_id', $shop->id)
            ->where('tiktok_username', $order->customer_username)
            ->where('status', CustomerMapping::STATUS_CONNECTED)
            ->latest('id')
            ->first();

        if (! $mapping) {
            return null;
        }

        $evaluation = $this->messengerSendService->evaluate($mapping);

        return MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $shop->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => $text,
            'status' => $evaluation['status'],
            'send_enabled' => config('facebook.send_enabled', false),
            'failure_reason' => $evaluation['reason'] ?? null,
            'preview_payload' => $this->messengerSendService->buildDraftPayload($mapping, $text),
        ]);
    }
}
