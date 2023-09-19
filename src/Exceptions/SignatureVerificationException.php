<?php

namespace WinLocalInc\Chjs\Exceptions;

class SignatureVerificationException extends \Exception
{
    protected ?string $httpBody;

    protected ?string $sigHeader;

    /**
     * Creates a new SignatureVerificationException exception.
     */
    public static function factory(string $message, string $httpBody = null, string $sigHeader = null): SignatureVerificationException
    {
        $instance = new static($message);
        $instance->setHttpBody($httpBody);
        $instance->setSigHeader($sigHeader);

        return $instance;
    }

    /**
     * Gets the HTTP body as a string.
     */
    public function getHttpBody(): ?string
    {
        return $this->httpBody;
    }

    /**
     * Sets the HTTP body as a string.
     */
    public function setHttpBody(string $httpBody = null): void
    {
        $this->httpBody = $httpBody;
    }

    /**
     * Gets HTTP header.
     */
    public function getSigHeader(): ?string
    {
        return $this->sigHeader;
    }

    /**
     * Sets HTTP header.
     */
    public function setSigHeader(string $sigHeader = null): void
    {
        $this->sigHeader = $sigHeader;
    }
}
