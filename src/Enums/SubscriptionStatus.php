<?php

namespace WinLocalInc\Chjs\Enums;

enum SubscriptionStatus: string
{
    use EnumHelpers;
    case Active = 'active';
    case Canceled = 'canceled';
    case Expired = 'expired';
    case OnHold = 'on_hold';
    case PastDue = 'past_due';
    case SoftFailure = 'soft_failure';
    case Trialing = 'trialing';
    case TrialEnded = 'trial_ended';
    case Unpaid = 'unpaid';
    case Suspended = 'suspended';
    case AwaitingSignup = 'awaiting_signup';
    case Assessing = 'assessing';
    case FailedToCreate = 'failed_to_create';
    case Paused = 'paused';
    case Pending = 'pending';
    case OnGracePeriod = 'on_grace_period';

    public function resumable(): bool
    {
        $resumableStatuses = [
            self::Canceled,
            self::OnGracePeriod,
        ];

        return in_array($this, $resumableStatuses);

    }
}
