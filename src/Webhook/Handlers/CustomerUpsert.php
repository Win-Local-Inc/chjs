<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use Illuminate\Support\Facades\DB;
use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::CustomerCreate,
    WebhookEvents::CustomerUpdate
)]
class CustomerUpsert extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        $reference = $payload['customer']['reference'] ?? null;
        $id = $payload['customer']['id'] ?? null;
        if ($reference) {
            DB::table('users')
                ->where(['user_id' => $reference])
                ->update(['chargify_id' => $id]);
        }
    }
}
