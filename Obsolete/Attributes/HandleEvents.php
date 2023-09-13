<?php

namespace Obsolete\Attributes;

use Attribute;
use Obsolete\Enums\WebhookEvents;

#[Attribute(Attribute::TARGET_CLASS)]
class HandleEvents
{
    public function __construct(
        public WebhookEvents ...$events
    ) {
    }
}
