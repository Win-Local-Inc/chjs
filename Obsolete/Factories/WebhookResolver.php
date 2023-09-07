<?php

namespace Obsolete\Factories;

use Obsolete\Attributes\HandleEvents;
use Obsolete\Enums\WebhookEvents;
use Obsolete\Interfaces\WebhookResolverInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class WebhookResolver implements WebhookResolverInterface
{
    protected ?WebhookEvents $event = null;

    public function getHandlersByEvent(WebhookEvents $event, array $paths): array
    {
        $this->event = $event;

        return Collection::make($paths)
            ->map(fn (string $path) => Collection::make($this->getAllFiles($path))
                ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
                ->map($this->createClassName(...))
                ->filter($this->classHandleEvent(...)))
            ->flatten()
            ->toArray();
    }

    protected function getAllFiles(string $path): array
    {
        return File::allFiles(App::path($path));
    }

    protected function createClassName(SplFileInfo $file): string
    {
        return ucfirst(
            str_replace([App::basePath().DIRECTORY_SEPARATOR, '/'], ['', '\\'], $file->getPath())
        ).'\\'.$file->getFilenameWithoutExtension();
    }

    protected function classHandleEvent(string $absoluteClassName): bool
    {
        $attributes = (new ReflectionClass($absoluteClassName))
            ->getAttributes(HandleEvents::class);

        return array_key_exists(0, $attributes) ? in_array($this->event, $attributes[0]->getArguments()) : false;
    }
}
