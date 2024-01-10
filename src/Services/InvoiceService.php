<?php

namespace WinLocalInc\Chjs\Services;

use Illuminate\Support\Collection;

class InvoiceService extends AbstractService
{
    public function createInvoice(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'line_items' => 'required|array',
        ]);

        return $this->post('subscriptions/'.$subscriptionId.'/invoices', ['invoice' => $parameters], true);
    }

    public function refundInvoice(string $invoiceId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'memo' => 'required|string',
            'payment_id' => 'required|string',
        ]);

        return $this->post('invoices/'.$invoiceId.'/refunds', ['refund' => $parameters], true);
    }

    public function reopenInvoice(string $invoiceId): array
    {
        return $this->post('invoices/'.$invoiceId.'/reopen', [], true);
    }

    public function voidInvoice(string $invoiceId, string $reason): array
    {
        return $this->post('invoices/'.$invoiceId.'/void', ['void' => ['reason' => $reason]], true);
    }

    public function issueInvoice(string $invoiceId, string $onFailedPayment = 'leave_open_invoice'): array
    {
        $this->validatePayload(['type' => $onFailedPayment], [
            'type' => 'required|in:leave_open_invoice,rollback_to_pending,initiate_dunning',
        ]);

        return $this->post('invoices/'.$invoiceId.'/issue', ['on_failed_payment' => $onFailedPayment], true);
    }

    public function sendInvoice(string $invoiceId, array $parameters = []): void
    {
        $this->validatePayload($parameters, [
            'recipient_emails' => 'sometimes|array|max:5',
            'cc_recipient_emails' => 'sometimes|array|max:5',
            'bcc_recipient_emails' => 'sometimes|array|max:5',
        ]);

        $this->post('invoices/'.$invoiceId.'/deliveries', $parameters, true);
    }

    public function createPayment(string $invoiceId, array $payment, string $type = 'external'): array
    {
        $this->validatePayload($payment, [
            'amount' => 'required|numeric',
        ]);

        $this->validatePayload(['type' => $type], [
            'type' => 'required|in:external,prepayment,service_credit,payment',
        ]);

        return $this->post('invoices/'.$invoiceId.'/payments', ['payment' => $payment, 'type' => $type], true);
    }

    public function createPaymentBulk(string $subscriptionId, array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'memo' => 'required|string',
            'payment_details' => 'required|string',
            'payment_method' => 'required|string|in:credit_card,check,cash,money_order,ach,other',
        ]);

        return $this->post('subscriptions/'.$subscriptionId.'/payments', ['payment' => $parameters], true);
    }

    public function createPaymentForMultipleInvoices(array $parameters): array
    {
        $this->validatePayload($parameters, [
            'amount' => 'required|numeric',
            'applications' => 'required|array',
            'applications.invoice_uid' => 'required|string',
            'applications.amount' => 'required|numeric',
        ]);

        return $this->post('invoices/payments', ['payment' => $parameters], true);
    }

    public function getInvoiceById(string $invoiceId): array
    {
        return $this->get('invoices/'.$invoiceId, [], true);
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

        $data = $this->get('invoices', $parameters, true);

        return collect(is_array($data) && array_key_exists('invoices', $data) ? $data['invoices'] : []);
    }

    public function listInvoicesEvents(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'invoice_uid' => 'sometimes|string',
        ]);

        $data = $this->get('invoices/events', $parameters, true);

        return collect(is_array($data) && array_key_exists('events', $data) ? $data['events'] : []);
    }

    public function listCreditNotes(array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'subscription_id' => 'sometimes|string',
        ]);

        $data = $this->get('credit_notes', $parameters, true);

        return collect(is_array($data) && array_key_exists('credit_notes', $data) ? $data['credit_notes'] : []);
    }

    public function listSegmentsForConsolidatedInvoice(string $invoiceId, array $parameters = []): Collection
    {
        $this->validatePayload($parameters, [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
            'direction' => 'sometimes|string|in:asc,desc',
        ]);

        $data = $this->get('invoices/'.$invoiceId.'/segments', $parameters, true);

        return collect(is_array($data) && array_key_exists('invoices', $data) ? $data['invoices'] : []);
    }

    public function getCreditNodeById(string $creditNoteId): array
    {
        return $this->get('credit_notes/'.$creditNoteId, [], true);
    }

    public function previewCustomerInformationChanges(string $invoiceId): array
    {
        return $this->post('invoices/'.$invoiceId.'/customer_information/preview', [], true);
    }

    public function updateCustomerInformation(string $invoiceId): array
    {
        return $this->put('invoices/'.$invoiceId.'/customer_information', [], true);
    }

    public function issueAdvanceInvoice(string $subscriptionId, bool $force = false): array
    {
        return $this->post('subscriptions/'.$subscriptionId.'/advance_invoice/issue', $force ? ['force' => $force] : [], true);
    }

    public function voidAdvanceInvoice(string $subscriptionId, string $reason): array
    {
        return $this->post('subscriptions/'.$subscriptionId.'/advance_invoice/void', ['void' => ['reason' => $reason]], true);
    }

    public function getAdvanceInvoice(string $subscriptionId): array
    {
        return $this->get('subscriptions/'.$subscriptionId.'/advance_invoice', [], true);
    }
}
