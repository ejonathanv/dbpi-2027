<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Models\CommercialQuote;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

class CommercialQuoteAuthorizationTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('auth_requires_save')
                    ->content('Guarde la cotización para ver el historial de autorizaciones.'),
            ];
        }

        return [
            Section::make()
                ->schema([
                    Placeholder::make('authorization_history')
                        ->label('')
                        ->content(fn ($record): HtmlString => new HtmlString(self::historyHtml($record))),
                ]),
        ];
    }

    protected static function historyHtml(?CommercialQuote $record): string
    {
        if (! $record) {
            return '<p class="text-sm text-gray-500">Sin datos.</p>';
        }

        $record->loadMissing(['authorizationRequests.requester', 'authorizationRequests.reviews.user']);

        $status = $record->authorization_status?->getLabel() ?? 'Borrador';

        $html = '<p class="text-sm"><strong>Estado:</strong> '.e($status).'</p>';

        if ($record->authorizationRequests->isEmpty()) {
            $html .= '<p class="mt-3 text-sm text-gray-500">Aún no hay solicitudes de autorización.</p>';

            return $html;
        }

        $html .= '<ul class="mt-4 space-y-4 text-sm">';
        foreach ($record->authorizationRequests as $request) {
            $html .= '<li class="rounded-lg border border-gray-200 p-3 dark:border-white/10">';
            $html .= '<div><strong>Solicitud</strong> · '.e($request->requested_at?->format('d/m/Y H:i') ?? '').'</div>';
            $html .= '<div class="text-gray-600 dark:text-gray-400">Por: '.e($request->requester?->name ?? '—').' · '.e($request->status->getLabel()).'</div>';
            $html .= '<ul class="mt-2 ms-4 list-disc text-gray-700 dark:text-gray-300">';
            foreach ($request->reviews as $review) {
                $html .= '<li>'.e($review->user?->name ?? '—').': '.e($review->status->getLabel());
                if (filled($review->notes)) {
                    $html .= ' — '.e($review->notes);
                }
                $html .= '</li>';
            }
            $html .= '</ul></li>';
        }
        $html .= '</ul>';

        return $html;
    }
}
