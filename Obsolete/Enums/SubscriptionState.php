<?php

namespace Obsolete\Enums;

enum SubscriptionState: string
{
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
}
