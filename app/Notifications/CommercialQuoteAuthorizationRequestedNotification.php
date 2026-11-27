<?php

namespace App\Notifications;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Models\CommercialQuoteAuthorizationRequest;
use App\Models\CommercialQuoteAuthorizationReview;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommercialQuoteAuthorizationRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommercialQuoteAuthorizationRequest $authorizationRequest,
        protected CommercialQuoteAuthorizationReview $review,
    ) {
        $this->authorizationRequest->loadMissing('quote');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $quote = $this->authorizationRequest->quote;

        $url = CommercialQuoteResource::getUrl('review-authorization', [
            'record' => $quote,
            'authorizationRequest' => $this->authorizationRequest,
        ]);

        return (new MailMessage)
            ->subject('Cotización pendiente de autorización')
            ->greeting('Hola '.$notifiable->name)
            ->line('Hay una cotización en espera de tu autorización.')
            ->line('Número: '.$quote?->quote_number)
            ->action('Revisar cotización', $url)
            ->line('Gracias.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $quote = $this->authorizationRequest->quote;

        $url = CommercialQuoteResource::getUrl('review-authorization', [
            'record' => $quote,
            'authorizationRequest' => $this->authorizationRequest,
        ]);

        return FilamentNotification::make()
            ->title('Autorización de cotización')
            ->body('La cotización '.($quote?->quote_number ?? '').' requiere tu validación.')
            ->actions([
                Action::make('review')
                    ->label('Revisar')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }
}
