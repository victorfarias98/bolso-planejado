<?php

namespace App\Enums;

enum PlanBillingMode: string
{
    case Free = 'free';
    case Subscription = 'subscription';
    case OneTime = 'one_time';
}
