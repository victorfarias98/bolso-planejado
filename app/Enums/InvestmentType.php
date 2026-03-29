<?php

namespace App\Enums;

enum InvestmentType: string
{
    case Pocket = 'pocket';
    case Cdb = 'cdb';
    case Treasury = 'treasury';
    case Fund = 'fund';
    case Stocks = 'stocks';
    case Crypto = 'crypto';
    case Other = 'other';
}
