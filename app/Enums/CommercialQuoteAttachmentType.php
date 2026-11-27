<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommercialQuoteAttachmentType: string implements HasColor, HasLabel
{
    case PurchaseOrder = 'purchase_order';
    case Invoice = 'invoice';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::PurchaseOrder => 'Orden de compra',
            self::Invoice => 'Factura',
            self::Other => 'Otro',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PurchaseOrder => 'primary',
            self::Invoice => 'success',
            self::Other => 'gray',
        };
    }
}