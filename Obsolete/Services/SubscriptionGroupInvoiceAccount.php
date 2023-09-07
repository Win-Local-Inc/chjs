<?php

namespace Obsolete\Services;

class SubscriptionGroupInvoiceAccount extends AbstractService
{
    public function createPrepayment(string $groupId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'details' => 'required|string',
            'memo' => 'required|string',
            'method' => 'required|string|in:check,cash,money_order,ach,paypal_account,other',
        ]);

        return $this->getClient()
            ->request('subscription_groups/'.$groupId.'/prepayments', 'post', ['prepayment' => $parameters])
            ->json();
    }

    public function issueServiceCredit(string $groupId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'memo' => 'required|string',
        ]);

        return $this->getClient()
            ->request('subscription_groups/'.$groupId.'/service_credits', 'post', ['service_credit' => $parameters])
            ->json();
    }

    public function deductServiceCredit(string $groupId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'memo' => 'required|string',
        ]);

        return $this->getClient()
            ->request('subscription_groups/'.$groupId.'/service_credit_deductions', 'post', ['deduction' => $parameters])
            ->json();
    }
}
