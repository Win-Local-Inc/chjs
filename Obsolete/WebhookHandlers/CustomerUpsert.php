<?php

namespace Obsolete\WebhookHandlers;

use App\Models\Chargify\ChargifyCustomer;
use App\Models\User;
use Obsolete\Attributes\HandleEvents;
use Obsolete\ChargifyUtility;
use Obsolete\Enums\WebhookEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

#[HandleEvents(
    WebhookEvents::CustomerCreate,
    WebhookEvents::CustomerUpdate
)]
class CustomerUpsert extends AbstractHandler
{
    protected function handleEvent(array $payload)
    {
        $this->validateData($payload);
        $this->updateUser($payload);
    }

    protected function validateData(array &$payload)
    {
        Validator::make($payload, [
            'customer' => 'required|array',
            'customer.id' => 'required|integer',
            'customer.email' => 'required|email',
        ])->validate();
    }

    protected function updateUser(array &$payload)
    {
        $data = $payload['customer'];
        $customer = ChargifyCustomer::find($data['id']);
        if (! $customer) {
            $user = User::where('email', $data['email'])->first();
            if (! $user) {
                return Log::notice(
                    'Chargify Webhook CustomerUpsert User Not Exists: '.$data['email'],
                    ['event_id' => $this->chargifyEvent->id]
                );
            }
            ChargifyCustomer::insertOrIgnore([
                'id' => $data['id'],
                'user_id' => $user->user_id,
                'created_at' => ChargifyUtility::getFixedDateTime($data['created_at']),
                'updated_at' => ChargifyUtility::getFixedDateTime($data['updated_at']),
            ]);
            $customer = ChargifyCustomer::find($data['id']);
        }

        if (array_key_exists('parent_id', $data)) {
            $customer->update(['parent_id' => $data['parent_id']]);
        }
    }
}
