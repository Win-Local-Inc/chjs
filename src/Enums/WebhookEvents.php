<?php

namespace WinLocalInc\Chjs\Enums;

enum WebhookEvents: string
{
    case BillingDateChange = 'billing_date_change';

    case ComponentAllocationChange = 'component_allocation_change';

    case CustomFieldValueChange = 'custom_field_value_change';

    case CustomerCreate = 'customer_create';
    case CustomerDelete = 'customer_delete';
    case CustomerUpdate = 'customer_update';

    case DelayedSubscriptionCreationFailure = 'delayed_subscription_creation_failure';
    case DelayedSubscriptionCreationSuccess = 'delayed_subscription_creation_success';

    case DirectDebitPaymentPaidOut = 'direct_debit_payment_paid_out';
    case DirectDebitPaymentPending = 'direct_debit_payment_pending';
    case DirectDebitPaymentRejected = 'direct_debit_payment_rejected';

    case DunningStepReached = 'dunning_step_reached';

    case ExpirationDateChange = 'expiration_date_change';

    case ExpiringCard = 'expiring_card';

    case InvoiceIssued = 'invoice_issued';

    case ItemPricePointChanged = 'item_price_point_changed';

    case MeteredUsage = 'metered_usage';

    case PaymentFailure = 'payment_failure';
    case PaymentSuccess = 'payment_success';

    case PendingCancellationChange = 'pending_cancellation_change';
    case PendingPaymentCreated = 'pending_payment_created';
    case PendingPaymentCompleted = 'pending_payment_completed';
    case PendingPaymentFailed = 'pending_payment_failed';

    case PrepaidSubscriptionBalanceChange = 'prepaid_subscription_balance_change';
    case PrepaidUsage = 'prepaid_usage';

    case RenewalFailure = 'renewal_failure';
    case RenewalSuccess = 'renewal_success';

    case SignupSuccess = 'signup_success';
    case SignupFailure = 'signup_failure';

    case StatementSettled = 'statement_settled';
    case StatementClosed = 'statement_closed';

    case SubscriptionBankAccountUpdate = 'subscription_bank_account_update';
    case SubscriptionCardUpdate = 'subscription_card_update';
    case SubscriptionGroupCardUpdate = 'subscription_group_card_update';
    case SubscriptionGroupSignupFailure = 'subscription_group_signup_failure';
    case SubscriptionGroupSignupSuccess = 'subscription_group_signup_success';
    case SubscriptionPrepaymentAccountBalanceChanged = 'subscription_prepayment_account_balance_changed';
    case SubscriptionProductChange = 'subscription_product_change';
    case SubscriptionServiceCreditAccountBalanceChanged = 'subscription_service_credit_account_balance_changed';
    case SubscriptionStateChange = 'subscription_state_change';

    case UpcomingRenewalNotice = 'upcoming_renewal_notice';

    case UpgradeDowngradeFailure = 'upgrade_downgrade_failure';
    case UpgradeDowngradeSuccess = 'upgrade_downgrade_success';
}
