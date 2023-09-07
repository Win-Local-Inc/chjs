<?php

namespace Obsolete\WebhookHandlers;

use App\Models\Chargify\ChargifyEvent;
use Obsolete\Chargify;
use Obsolete\ChargifySystem;
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

    protected ChargifyEvent $chargifyEvent;

    public function __construct(protected int $id)
    {
    }

    public function handle()
    {
        $this->chargifyEvent = ChargifyEvent::findOrFail($this->id);
        $this->handleEvent($this->chargifyEvent->payload);
    }

    protected function getChargify(): Chargify
    {
        return resolve(Chargify::class);
    }

    protected function getChargifySystem(): ChargifySystem
    {
        return resolve(ChargifySystem::class);
    }

    abstract protected function handleEvent(array $payload);
}
