<?php

namespace Obsolete;

use App\Models\Chargify\ChargifyEvent;
use Obsolete\Enums\WebhookEvents;
use Obsolete\Interfaces\WebhookResolverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ChargifyWebhook
{
    protected static string $path = 'Services/Chargify/WebhookHandlers';

    protected string $id;

    protected string $event;

    protected array $payload;

    public function __construct(protected WebhookResolverInterface $webhookResolver)
    {
    }

    public function handle(array $data)
    {
        $this->validateAndUnpackArray($data);

        Cache::lock('chargify_event_id_'.$this->id, 60)->get(function () {
            if (ChargifyEvent::where('id', $this->id)->exists()) {
                return;
            }

            ChargifyEvent::create([
                'id' => $this->id,
                'event_name' => $this->event,
                'payload' => $this->payload,
            ]);

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
        $classes = $this->webhookResolver->getHandlersByEvent($event, [self::$path]);
        foreach ($classes as $class) {
            $class::dispatch($this->id);
        }
    }
}
