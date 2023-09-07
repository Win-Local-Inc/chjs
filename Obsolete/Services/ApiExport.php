<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class ApiExport extends AbstractService
{
    public function createInvoicesExport(): array
    {
        return $this->getClient()
            ->request('api_exports/invoices', 'post')
            ->json('batchjob', []);
    }

    public function createProformaInvoicesExport(): array
    {
        return $this->getClient()
            ->request('api_exports/proforma_invoices', 'post')
            ->json('batchjob', []);
    }

    public function createSubscriptionsExport(): array
    {
        return $this->getClient()
            ->request('api_exports/subscriptions', 'post')
            ->json('batchjob', []);
    }

    public function getStateOfInvoicesExport(string $batchId): array
    {
        return $this->getClient()
            ->request('api_exports/invoices/'.$batchId, 'get')
            ->json('batchjob', []);
    }

    public function getStateOfProformaInvoicesExport(string $batchId): array
    {
        return $this->getClient()
            ->request('api_exports/proforma_invoices/'.$batchId, 'get')
            ->json('batchjob', []);
    }

    public function getStateOfSubscriptionsExport(string $batchId): array
    {
        return $this->getClient()
            ->request('api_exports/subscriptions/'.$batchId, 'get')
            ->json('batchjob', []);
    }

    public function listInvoicesExport(string $batchId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('api_exports/invoices/'.$batchId.'/rows', 'get', $parameters)
            ->collect();
    }

    public function listProformaInvoicesExport(string $batchId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('api_exports/proforma_invoices/'.$batchId.'/rows', 'get', $parameters)
            ->collect();
    }

    public function listSubscriptionsExport(string $batchId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        return $this->getClient()
            ->request('api_exports/subscriptions/'.$batchId.'/rows', 'get', $parameters)
            ->collect();
    }
}
