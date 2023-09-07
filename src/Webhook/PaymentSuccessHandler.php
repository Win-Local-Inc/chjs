<?php

namespace WinLocalInc\Chjs\Webhook;

class PaymentSuccessHandler
{


    public function handle($payload)
    {
        ray('PaymentSuccessHandler', $payload);
    }
}
