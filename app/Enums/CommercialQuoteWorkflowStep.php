<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommercialQuoteWorkflowStep: string implements HasLabel
{
    case InProgress = 'in_progress';
    case WaitingInternalAuthorization = 'waiting_internal_authorization';
    case InternallyAuthorized = 'internally_authorized';
    case AwaitingClientAuthorization = 'awaiting_client_authorization';
    case NegotiationReview = 'negotiation_review';
    case Cancelled = 'cancelled';
    case ClientAuthorized = 'client_authorized';

    public function getLabel(): string
    {
        return match ($this) {
            self::InProgress => 'En proceso',
            self::WaitingInternalAuthorization => 'Esperando autorización',
            self::InternallyAuthorized => 'Autorizada internamente',
            self::AwaitingClientAuthorization => 'En espera de autorización del cliente',
            self::NegotiationReview => 'Negociación/Revisión',
            self::Cancelled => 'Cancelada',
            self::ClientAuthorized => 'Autorizada por cliente',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::InProgress => 1,
            self::WaitingInternalAuthorization => 2,
            self::InternallyAuthorized => 3,
            self::AwaitingClientAuthorization => 4,
            self::NegotiationReview => 5,
            self::Cancelled => 6,
            self::ClientAuthorized => 7,
        };
    }

    /**
     * @return list<self>
     */
    public static function pipeline(): array
    {
        return [
            self::InProgress,
            self::WaitingInternalAuthorization,
            self::InternallyAuthorized,
            self::AwaitingClientAuthorization,
            self::NegotiationReview,
            self::Cancelled,
            self::ClientAuthorized,
        ];
    }
}