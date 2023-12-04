<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;
use WinLocalInc\Chjs\Models\Metafield;
use WinLocalInc\Chjs\Models\Subscription;

#[HandleEvents(
    WebhookEvents::CustomFieldValueChange
)]
class MetafieldUpdate extends AbstractHandler
{
    public const Created = 'created';

    public const Deleted = 'deleted';

    protected function handleEvent(string $event, array $payload)
    {
        $metafieldData = $payload['metafield'];

        if ($metafieldData['resource_type'] !== 'Subscription') {
            return;
        }

        $eventType = $metafieldData['event_type'];
        $subscriptionId = $metafieldData['resource_id'];
        $metaKey = $metafieldData['metafield_name'];
        $metaValue = $eventType === self::Created ?
            $metafieldData['new_value'] : $metafieldData['old_value'];

        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $sha1 = sha1($metaKey.mb_strtolower($metaValue));
        $metafield = Metafield::where('sha1_hash', $sha1)->first();
        if (! $metafield) {
            $metafield = Metafield::create([
                'key' => $metaKey,
                'value' => $metaValue,
                'sha1_hash' => $sha1,
            ]);
        }

        if ($eventType === self::Created) {
            $subscription->metafields()->attach($metafield);
        } else {
            $subscription->metafields()->detach($metafield);
        }
    }
}
