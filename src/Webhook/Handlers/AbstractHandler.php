<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $id,
        protected string $event,
        protected array $payload
    ) {
    }

    public function handle()
    {
        $this->handleEvent($this->event, $this->payload);
    }

    abstract protected function handleEvent(string $event, array $payload);
}
