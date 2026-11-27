<?php

namespace App\Enums;

enum CommercialQuoteMarginOverrideType: string
{
    case CustomerCost = 'customer_cost';
    case CustomerAmount = 'customer_amount';
    case MarginPercent = 'margin_percent';
    case MarginAmount = 'margin_amount';
}
