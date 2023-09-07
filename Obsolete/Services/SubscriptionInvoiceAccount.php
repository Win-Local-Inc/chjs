<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class SubscriptionInvoiceAccount extends AbstractService
{
    public function createPrepayment(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'details' => 'required|string',
            'memo' => 'required|string',
            'method' => 'required|string|in:check,cash,money_order,ach,paypal_account,other',
            'payment_profile_id' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/prepayments', 'post', ['prepayment' => $parameters])
            ->json();
    }

    public function issueServiceCredit(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'memo' => 'required|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/service_credits', 'post', ['service_credit' => $parameters])
            ->json();
    }

    public function deductServiceCredit(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|integer',
            'memo' => 'required|string',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/service_credit_deductions', 'post', ['deduction' => $parameters])
            ->json();
    }

    public function refundPrepayment(string $subscriptionId, string $prepaymentId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required_without:amount_in_cents|integer',
            'amount_in_cents' => 'required_without:amount|integer',
            'memo' => 'required|string',
            'external' => 'sometimes|boolean',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/prepayments/'.$prepaymentId.'/refunds', 'post', ['refund' => $parameters])
            ->json('prepayment', []);
    }

    public function getAccountBalances(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/account_balances', 'get')
            ->json();
    }

    public function listPrepayments(string $subscriptionId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/account_balances', 'get', $parameters)
            ->collect('prepayments');
    }
}
