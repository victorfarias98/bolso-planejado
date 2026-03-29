<?php

namespace App\Enums;

enum DebtStatus: string
{
    case Open = 'open';
    case Negotiation = 'negotiation';
    case AgreementActive = 'agreement_active';
    case PaidOff = 'paid_off';
}
