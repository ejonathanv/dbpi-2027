<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommercialQuoteLineItemType: string implements HasLabel
{
    case Product = 'product';
    case Personnel = 'personnel';
    case Contract = 'contract';

    public function getLabel(): string
    {
        return match ($this) {
            self::Product => 'Producto',
            self::Personnel => 'Recurso',
            self::Contract => 'Contrato',
        };
    }
}
