<?php

namespace WinLocalInc\Chjs\Webhook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use WinLocalInc\Chjs\Enums\WebhookEvents;

class ChargifyWebhook
{
    protected string $id;

    protected string $event;

    protected array $payload;

    public function __construct(protected WebhookResolver $webhookResolver) {}

    public function handle(array $data)
    {
        $this->validateAndUnpackArray($data);

        Cache::lock('chargify_event_id_' . $this->id, 60)->get(function () {

            $eventEnum = WebhookEvents::tryFrom($this->event);
            if ($eventEnum) {
                $this->handlers($eventEnum);
            }
        });
    }

    protected function validateAndUnpackArray(array &$data)
    {
        Validator::make($data, [
            'id' => 'required|numeric',
            'event' => 'required|string',
            'payload' => 'required|array',
        ])->validate();

        ['id' => $this->id,
            'event' => $this->event,
            'payload' => $this->payload
        ] = $data;
    }

    protected function handlers(WebhookEvents $event): void
    {
        $classes = $this->webhookResolver->getHandlersByEvent($event);
        foreach ($classes as $class) {
            $class::dispatch($this->id, $this->event, $this->payload)
                ->onConnection(config('chjs.webhook_queue'));
        }
    }
}
