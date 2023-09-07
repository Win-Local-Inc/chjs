<?php

namespace Obsolete\Services;

use Illuminate\Support\Collection;

class Invoice extends AbstractService
{
    public function createInvoice(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'line_items' => 'required|array',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/invoices', 'post', ['invoice' => $parameters])
            ->json('invoice', []);
    }

    public function refundInvoice(string $invoiceId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'memo' => 'required|string',
            'payment_id' => 'required|string',
        ]);

        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/refunds', 'post', ['refund' => $parameters])
            ->json();
    }

    public function reopenInvoice(string $invoiceId): array
    {
        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/reopen', 'post')
            ->json();
    }

    public function voidInvoice(string $invoiceId, string $reason): array
    {
        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/void', 'post', ['void' => ['reason' => $reason]])
            ->json();
    }

    public function issueInvoice(string $invoiceId, string $onFailedPayment = 'leave_open_invoice'): array
    {
        $this->validatePayload(['type' => $onFailedPayment], [
            'type' => 'required|in:leave_open_invoice,rollback_to_pending,initiate_dunning',
        ]);

        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/issue', 'post', ['on_failed_payment' => $onFailedPayment])
            ->json();
    }

    public function sendInvoice(string $invoiceId, array $parameters = []): void
    {
        $this->validatePayload($parameters, [
            'recipient_emails' => 'sometimes|array|max:5',
            'cc_recipient_emails' => 'sometimes|array|max:5',
            'bcc_recipient_emails' => 'sometimes|array|max:5',
        ]);

        $this->getClient()
            ->request('invoices/'.$invoiceId.'/deliveries', 'post', $parameters);
    }

    public function createPayment(string $invoiceId, array $payment, string $type = 'external'): array
    {
        $this->validatePayload($payment, [
            'amount' => 'required|numeric',
        ]);

        $this->validatePayload(['type' => $type], [
            'type' => 'required|in:external,prepayment,service_credit,payment',
        ]);

        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/payments', 'post', ['payment' => $payment, 'type' => $type])
            ->json();
    }

    public function createPaymentBulk(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'memo' => 'required|string',
            'payment_details' => 'required|string',
            'payment_method' => 'required|string|in:credit_card,check,cash,money_order,ach,other',
        ]);

        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/payments', 'post', ['payment' => $parameters])
            ->json();
    }

    public function createPaymentForMultipleInvoices(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'applications' => 'required|array',
            'applications.invoice_uid' => 'required|string',
            'applications.amount' => 'required|numeric',
        ]);

        return $this->getClient()
            ->request('invoices/payments', 'post', ['payment' => $parameters])
            ->json('payment', []);
    }

    public function getInvoiceById(string $invoiceId): array
    {
        return $this->getClient()
            ->request('invoices/'.$invoiceId, 'get')
            ->json();
    }

    public function listInvoices(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'date_field' => 'sometimes|string|in:created_at,due_date,issue_date,updated_at,paid_date',
            'direction' => 'sometimes|string|in:asc,desc',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
            'start_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
            'end_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        return $this->getClient()
            ->request('invoices', 'get', $parameters)
            ->collect('invoices');
    }

    public function listInvoicesEvents(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'invoice_uid' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('invoices/events', 'get', $parameters)
            ->collect('events');
    }

    public function listCreditNotes(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'subscription_id' => 'sometimes|string',
        ]);

        return $this->getClient()
            ->request('credit_notes', 'get', $parameters)
            ->collect('credit_notes');
    }

    public function listSegmentsForConsolidatedInvoice(string $invoiceId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
        ]);

        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/segments', 'get', $parameters)
            ->collect('invoices');
    }

    public function getCreditNodeById(string $creditNoteId): array
    {
        return $this->getClient()
            ->request('credit_notes/'.$creditNoteId, 'get')
            ->json();
    }

    public function previewCustomerInformationChanges(string $invoiceId): array
    {
        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/customer_information/preview', 'post')
            ->json();
    }

    public function updateCustomerInformation(string $invoiceId): array
    {
        return $this->getClient()
            ->request('invoices/'.$invoiceId.'/customer_information', 'put')
            ->json();
    }

    public function issueAdvanceInvoice(string $subscriptionId, bool $force = false): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/advance_invoice/issue', 'post', $force ? ['force' => $force] : [])
            ->json();
    }

    public function voidAdvanceInvoice(string $subscriptionId, string $reason): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/advance_invoice/void', 'post', ['void' => ['reason' => $reason]])
            ->json();
    }

    public function getAdvanceInvoice(string $subscriptionId): array
    {
        return $this->getClient()
            ->request('subscriptions/'.$subscriptionId.'/advance_invoice', 'get')
            ->json();
    }
}
