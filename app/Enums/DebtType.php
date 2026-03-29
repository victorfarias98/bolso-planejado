<?php

namespace App\Enums;

enum DebtType: string
{
    case Card = 'card';
    case Loan = 'loan';
    case Personal = 'personal';
    case Store = 'store';
    case Family = 'family';
    case Other = 'other';
}
