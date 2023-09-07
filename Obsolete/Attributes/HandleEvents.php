<?php

namespace Obsolete\Attributes;

use Obsolete\Enums\WebhookEvents;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class HandleEvents
{
    public function __construct(
        public WebhookEvents ...$events
    ) {
    }
}
