<?php

namespace WinLocalInc\Chjs\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use WinLocalInc\Chjs\Exceptions\SignatureVerificationException;
use WinLocalInc\Chjs\Webhook\WebhookSignature;

class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle( $request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                // $request->headers->get
                $request->header('x-chargify-webhook-signature-hmac-sha-256'),
                config('chjs.shared_key'),
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
        
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
