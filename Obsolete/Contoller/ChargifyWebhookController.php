<?php

namespace App\Http\Controllers\Chargify;

use App\Services\Chargify\ChargifyWebhook;
use App\Services\Chargify\Middleware\VerifyChargifySignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChargifyWebhookController extends Controller
{
    public function __construct(protected ChargifyWebhook $chargifyWebhook)
    {
        $this->middleware(VerifyChargifySignature::class);
    }

    public function handleWebhook(Request $request)
    {
        $this->chargifyWebhook->handle($request->all());

        return new JsonResponse(['message' => 'Webhook Handled'], 200);
    }
}
