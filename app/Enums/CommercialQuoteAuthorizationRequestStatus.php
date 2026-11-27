<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommercialQuoteAuthorizationRequestStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ChangesSuggested = 'changes_suggested';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'En curso',
            self::Approved => 'Aprobada',
            self::Rejected => 'Rechazada',
            self::ChangesSuggested => 'Cambios solicitados',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::ChangesSuggested => 'info',
        };
    }
}
