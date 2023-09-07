<?php

namespace Obsolete\Interfaces;

use Obsolete\Enums\WebhookEvents;

interface WebhookResolverInterface
{
    public function getHandlersByEvent(WebhookEvents $event, array $paths): array;
}
