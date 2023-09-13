<?php

namespace WinLocalInc\Chjs\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use WinLocalInc\Chjs\Webhook\ChargifyWebhook;

class WebhookController
{
    public function __construct(protected ChargifyWebhook $chargifyWebhook)
    {
    }

    public function handleWebhook(Request $request)
    {
        $this->chargifyWebhook->handle($request->all());

        return new JsonResponse(['message' => 'Webhook Handled'], 200);
    }
}
