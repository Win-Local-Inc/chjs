<?php

namespace WinLocalInc\Chjs\Webhook;

class ChargifyPayload
{
    public object $payload;

    /**
     * ChargifyPayload constructor.
     */
    public function __construct(mixed $payload)
    {
        $this->payload = $this->cast($payload);
    }

    protected function cast(mixed $payload): mixed
    {
        if (! is_array($payload)) {
            return $payload;
        }

        foreach ($payload as &$item) {
            $item = $this->cast($item);
        }

        return (object) $payload;
    }
}
