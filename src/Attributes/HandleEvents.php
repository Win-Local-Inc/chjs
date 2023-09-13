<?php

namespace WinLocalInc\Chjs\Attributes;

use Attribute;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[Attribute(Attribute::TARGET_CLASS)]
class HandleEvents
{
    public function __construct(
        public WebhookEvents ...$events
    ) {
    }
}
