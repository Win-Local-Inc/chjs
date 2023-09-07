<?php

namespace Obsolete\Middleware;

use Obsolete\ChargifyConfig;
use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyChargifySignature
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        $hashWeb = $request->headers->get('X-Chargify-Webhook-Signature-Hmac-Sha-256');
        $hashLocal = hash_hmac('sha256', $request->getContent(), $this->getChargifyConfig()->getSharedKey());

        if (! $hashWeb || ! hash_equals($hashWeb, $hashLocal)) {
            throw new AccessDeniedHttpException('Wrong Signature');
        }

        return $next($request);
    }

    protected function getChargifyConfig(): ChargifyConfig
    {
        return resolve(ChargifyConfig::class);
    }
}
