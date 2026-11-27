<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommercialQuoteAuthorizationStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case PendingAuthorization = 'pending_authorization';
    case Authorized = 'authorized';
    case Rejected = 'rejected';
    case ChangesRequested = 'changes_requested';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::PendingAuthorization => 'En autorización',
            self::Authorized => 'Autorizada',
            self::Rejected => 'Rechazada',
            self::ChangesRequested => 'Cambios solicitados',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingAuthorization => 'warning',
            self::Authorized => 'success',
            self::Rejected => 'danger',
            self::ChangesRequested => 'info',
        };
    }
}
