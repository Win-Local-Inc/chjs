<?php

namespace WinLocalInc\Chjs\Http\Controllers;

use WinLocalInc\Chjs\Chargify\ObjectTypes;
use WinLocalInc\Chjs\Chargify\Webhook\Webhook;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;


class WebhookController extends Controller
{
    const HANDLER_NAMESPACE = 'WinLocalInc\\Chjs\\Webhook\\';

    public function __invoke():  Response
    {

        $content = request()->all();
        $handlerClass = static::HANDLER_NAMESPACE . Str::studly($content['event']) . 'Handler';

//        $payload = (new ChargifyPayload($content['payload']))->payload;
        ray($handlerClass)->green();
        $payload = ObjectTypes::resolve($content['payload'], Webhook::class);

        ray($payload, $handlerClass)->red();
//        WebhookReceived::dispatch($payload);
        if (class_exists($handlerClass)) {
            $handler = new $handlerClass();

            if ($handler->handle($payload)) {
//                WebhookHandled::dispatch($payload);
                return new Response('Webhook Handled', 200);
            }

        }

        return new Response;
    }

}
