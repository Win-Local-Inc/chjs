<?php

namespace WinLocalInc\Chjs\Webhook\Handlers;

use WinLocalInc\Chjs\Attributes\HandleEvents;
use WinLocalInc\Chjs\Enums\WebhookEvents;

#[HandleEvents(
    WebhookEvents::ComponentAllocationChange
)]
class ComponentAllocation extends AbstractHandler
{
    protected function handleEvent(string $event, array $payload)
    {
        ray($event, $payload);
    }
}
/**
  {
    "memo": null,
    "site": {
        "id": "84009",
        "subdomain": "win-local-dev"
    },
    "renews": "false",
    "payment": {
        "id": "923913916",
        "memo": "Payment for: Prorated component allocation changes.",
        "success": "true",
        "amount_in_cents": "9200"
    },
    "product": {
        "id": "6542053",
        "name": "Franchise",
        "interval": "1",
        "interval_unit": "month"
    },
    "event_id": "3531722589",
    "component": {
        "id": "2364087",
        "kind": "prepaid_usage_component",
        "name": "Ads",
        "handle": "ads",
        "recurring": "false",
        "unit_name": "usd"
    },
    "timestamp": "2023-09-20T13:15:04Z",
    "allocation": {
        "id": "647363567",
        "proration_upgrade_scheme": "full-price-attempt-capture",
        "proration_downgrade_scheme": "no-prorate"
    },
    "expires_at": "2023-10-20 09:04:23 -0400",
    "total_used": "0",
    "subscription": {
        "id": "68496580",
        "name": "Mood Charge",
        "state": "active",
        "product": {
            "id": "6542053",
            "name": "testPrice",
            "interval": "1",
            "interval_unit": "month",
            "product_price_point_id": "2474178",
            "product_price_point_handle": "testprice"
        },
        "organization": null
    },
    "total_overage": "0",
    "new_allocation": "100",
    "price_point_id": "3081430",
    "allocated_quantity": "100",
    "previous_allocation": "0",
    "transaction_exchange_rate": {
        "rate_type": null,
        "actual_rate": "1",
        "reporting_rate": "1"
    }
}
 */
