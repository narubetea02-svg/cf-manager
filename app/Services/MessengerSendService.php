<?php

namespace App\Services;

use App\Models\CustomerMapping;
use App\Models\MessengerReplyDraft;
use App\Models\MessengerSetting;

class MessengerSendService
{
    public function __construct(
        protected FacebookService $facebookService
    ) {
    }

    public function buildDraftPayload(CustomerMapping $mapping, string $text): array
    {
        return [
            'recipient' => ['id' => $mapping->facebook_psid],
            'message' => ['text' => $text],
            'page_id' => $mapping->facebook_page_id,
        ];
    }

    public function evaluate(CustomerMapping $mapping): array
    {
        $pageId = (string) ($mapping->facebook_page_id ?: '');
        $psid = (string) ($mapping->facebook_psid ?: '');
        $allowlist = config('facebook.send_test_psid_allowlist', []);

        if (! config('facebook.send_enabled', false)) {
            return [
                'status' => MessengerReplyDraft::STATUS_BLOCKED_BY_FLAG,
                'reason' => 'MESSENGER_SEND_ENABLED=false',
            ];
        }

        if ($psid === '') {
            return [
                'status' => MessengerReplyDraft::STATUS_FAILED,
                'reason' => 'missing_psid',
            ];
        }

        if (! in_array($psid, $allowlist, true)) {
            return [
                'status' => MessengerReplyDraft::STATUS_BLOCKED_BY_ALLOWLIST,
                'reason' => 'psid_not_in_allowlist',
            ];
        }

        $setting = MessengerSetting::where('shop_id', $mapping->shop_id)
            ->where('is_active', true)
            ->when($pageId !== '', fn ($query) => $query->where('fb_page_id', $pageId))
            ->latest('id')
            ->first();

        if (! $setting && $pageId !== '') {
            $setting = MessengerSetting::where('shop_id', $mapping->shop_id)
                ->where('is_active', true)
                ->latest('id')
                ->first();
        }

        if (! $setting || ! $setting->fb_page_token) {
            return [
                'status' => MessengerReplyDraft::STATUS_FAILED,
                'reason' => 'missing_page_token',
            ];
        }

        return [
            'status' => MessengerReplyDraft::STATUS_SENT,
            'reason' => null,
            'page_id' => $setting->fb_page_id ?: $pageId,
            'page_token' => $setting->fb_page_token,
        ];
    }

    public function sendDraft(CustomerMapping $mapping, MessengerReplyDraft $draft): array
    {
        $evaluation = $this->evaluate($mapping);
        $payload = $this->buildDraftPayload($mapping, $draft->draft_text);

        if ($evaluation['status'] !== MessengerReplyDraft::STATUS_SENT) {
            return [
                'status' => $evaluation['status'],
                'payload' => $payload,
                'response' => null,
                'reason' => $evaluation['reason'] ?? null,
            ];
        }

        if (trim($draft->draft_text) === '') {
            return [
                'status' => MessengerReplyDraft::STATUS_FAILED,
                'payload' => $payload,
                'response' => null,
                'reason' => 'empty_message',
            ];
        }

        $response = $this->facebookService->sendPageMessageDetailed(
            $evaluation['page_token'],
            $mapping->facebook_psid,
            $draft->draft_text
        );

        return [
            'status' => !empty($response['ok']) ? MessengerReplyDraft::STATUS_SENT : MessengerReplyDraft::STATUS_FAILED,
            'payload' => $payload,
            'response' => [
                'page_id' => $evaluation['page_id'] ?? null,
                'sent' => !empty($response['ok']),
                'provider_message_id' => $response['provider_message_id'] ?? null,
                'status' => $response['status'] ?? null,
                'raw' => $response['response'] ?? [],
            ],
            'reason' => !empty($response['ok']) ? null : 'facebook_send_failed',
        ];
    }
}
