<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class ProformaInvoice extends AbstractService
{
    public function create(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/proforma_invoices', 'post')
            ->json();
    }

    public function createPreview(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/proforma_invoices/preview', 'post')
            ->json();
    }

    public function createConsolidated(string $subscriptionGroupId): array
    {
        return $this->getClient()
            ->request('subscription_groups/'.$subscriptionGroupId.'/proforma_invoices', 'post')
            ->json();
    }

    public function createSignup(array $parameters): array
    {
        return $this->getClient()
            ->request('subscriptions/proforma_invoices', 'post', $parameters)
            ->json();
    }

    public function createSignupPreview(array $parameters): array
    {
        return $this->getClient()
            ->request('subscriptions/proforma_invoices/preview', 'post', $parameters)
            ->json('proforma_invoice_preview', []);
    }

    public function listSubscriptionGroup(string $subscriptionGroupId): Collection
    {
        return $this->getClient()
            ->request('subscription_groups/'.$subscriptionGroupId.'/proforma_invoices', 'get')
            ->collect('proforma_invoices');
    }

    public function list(string $subscriptionId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/proforma_invoices', 'get', $parameters)
            ->collect('proforma_invoices');
    }

    public function getById(string $proformaInvoiceId): array
    {
        return $this->getClient()
            ->request('proforma_invoices/'.$proformaInvoiceId, 'get')
            ->json();
    }

    public function void(string $proformaInvoiceId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'reason' => 'required|string',
        ]);

        return $this->getClient()
            ->request('proforma_invoices/'.$proformaInvoiceId.'/void', 'post', ['void' => $parameters])
            ->json();
    }
}
