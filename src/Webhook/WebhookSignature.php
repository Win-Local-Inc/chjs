<?php

namespace WinLocalInc\Chjs\Webhook;

use WinLocalInc\Chjs\Exceptions\SignatureVerificationException;

class WebhookSignature
{

    /**
     * @throws SignatureVerificationException
     */
    public static function verifyHeader($payload, $header, $secret): void
    {
        if(! $header)
        {
            throw SignatureVerificationException::factory(
                'No signatures header found with expected scheme',
                $payload,
                $header
            );
        }

        if (! hash_equals($header, hash_hmac('sha256', $payload, $secret))) {
            throw SignatureVerificationException::factory(
                'No signatures found matching the expected signature for payload',
                $payload,
                $header
            );
        }
    }

}
