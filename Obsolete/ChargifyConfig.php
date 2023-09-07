<?php

namespace Obsolete;

class ChargifyConfig
{
    public function __construct(
        protected string $hostname,
        protected string $eventsHostname,
        protected string $subdomain,
        protected string $apiKey,
        protected string $publicKey,
        protected string $privateKey,
        protected string $sharedKey,
        protected int $timeout
    ) {
    }

    public function setHostname(string $hostname): static
    {
        $this->hostname = rtrim(trim($hostname), '/').'/';

        return $this;
    }

    public function setEventsHostname(string $eventsHostname): static
    {
        $this->eventsHostname = rtrim(trim($eventsHostname), '/').'/';

        return $this;
    }

    public function setSubdomain(string $subdomain): static
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function setPublicKey(string $publicKey): static
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function setPrivateKey(string $privateKey): static
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function setSharedKey(string $sharedKey): static
    {
        $this->sharedKey = $sharedKey;

        return $this;
    }

    public function setTimeout(array $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getEventsHostname(): string
    {
        return $this->eventsHostname;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    public function getSharedKey(): string
    {
        return $this->sharedKey;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
