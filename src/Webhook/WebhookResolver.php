<?php

namespace WinLocalInc\Chjs\Webhook;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;
use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

class WebhookResolver
{
    protected ?WebhookEvents $event = null;

    protected const Handlers = 'Handlers';

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
        return File::allFiles(__DIR__.DIRECTORY_SEPARATOR.self::Handlers);
    }

    protected function createClassName(SplFileInfo $file): string
    {
        return __NAMESPACE__.'\\'.self::Handlers.'\\'.$file->getFilenameWithoutExtension();
    }

    protected function classHandleEvent(string $absoluteClassName): bool
    {
        $attributes = (new ReflectionClass($absoluteClassName))
            ->getAttributes(HandleEvents::class);

        return array_key_exists(0, $attributes) ? in_array($this->event, $attributes[0]->getArguments()) : false;
    }
}
