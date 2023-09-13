<?php

namespace WinLocalInc\Chjs\Webhook;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;
use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

class WebhookResolver
{
    protected ?WebhookEvents $event = null;

    public function getHandlersByEvent(WebhookEvents $event): array
    {
        $this->event = $event;

        return Collection::make($this->getAllFiles())
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->map($this->createClassName(...))
            ->filter($this->classHandleEvent(...))
            ->toArray();
    }

    protected function getAllFiles(): array
    {
        return File::allFiles(App::basePath().'/vendor/win-local-inc/chjs/src/Webhook/Handlers');
    }

    protected function createClassName(SplFileInfo $file): string
    {
        return 'WinLocalInc\\Chjs\\Webhook\\Handlers\\'.$file->getFilenameWithoutExtension();
    }

    protected function classHandleEvent(string $absoluteClassName): bool
    {
        $attributes = (new ReflectionClass($absoluteClassName))
            ->getAttributes(HandleEvents::class);

        return array_key_exists(0, $attributes) ? in_array($this->event, $attributes[0]->getArguments()) : false;
    }
}
