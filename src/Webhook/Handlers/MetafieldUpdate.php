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

    public const Updated = 'updated';

    public const Deleted = 'deleted';

    protected function handleEvent(string $event, array $payload)
    {
        $metafieldData = $payload['metafield'];

        if ($metafieldData['resource_type'] !== 'Subscription') {
            return;
        }

        $subscriptionId = $metafieldData['resource_id'];
        $subscription = Subscription::where('subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            return;
        }

        match ($metafieldData['event_type']) {
            self::Created => $this->createEvent($subscription, $metafieldData),
            self::Updated => $this->updateEvent($subscription, $metafieldData),
            self::Deleted => $this->deleteEvent($subscription, $metafieldData)
        };
    }

    protected function getMetafield(string $metaKey, string $metaValue): Metafield
    {
        $sha1 = sha1($metaKey.mb_strtolower($metaValue));
        $metafield = Metafield::where('sha1_hash', $sha1)->first();
        if (! $metafield) {
            $metafield = Metafield::create([
                'key' => $metaKey,
                'value' => $metaValue,
                'sha1_hash' => $sha1,
            ]);
        }

        return $metafield;
    }

    protected function createEvent(Subscription $subscription, array $metafieldData)
    {
        $metaKey = $metafieldData['metafield_name'];
        $metaValue = $metafieldData['new_value'];
        $metafield = $this->getMetafield($metaKey, $metaValue);
        $subscription->metafields()->attach($metafield);
    }

    protected function updateEvent(Subscription $subscription, array $metafieldData)
    {
        $metaKey = $metafieldData['metafield_name'];
        $metaNewValue = $metafieldData['new_value'];
        $metaOldValue = $metafieldData['old_value'];
        $metafieldNew = $this->getMetafield($metaKey, $metaNewValue);
        $metafieldOld = $this->getMetafield($metaKey, $metaOldValue);
        $subscription->metafields()->attach($metafieldNew);
        $subscription->metafields()->detach($metafieldOld);
    }

    protected function deleteEvent(Subscription $subscription, array $metafieldData)
    {
        $metaKey = $metafieldData['metafield_name'];
        $metaValue = $metafieldData['old_value'];
        $metafield = $this->getMetafield($metaKey, $metaValue);
        $subscription->metafields()->detach($metafield);
    }
}
