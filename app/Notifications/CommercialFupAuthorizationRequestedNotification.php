<?php

namespace App\Notifications;

use App\Models\CommercialFupAuthorizationRequest;
use App\Models\CommercialFupAuthorizationReview;
use App\Support\CommercialFupPanelUrls;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommercialFupAuthorizationRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommercialFupAuthorizationRequest $authorizationRequest,
        protected CommercialFupAuthorizationReview $review,
    ) {
        $this->authorizationRequest->loadMissing('fup');
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
        $fup = $this->authorizationRequest->fup;
        $url = $fup
            ? CommercialFupPanelUrls::reviewAuthorization($fup, $this->authorizationRequest)
            : url('/admin');

        return (new MailMessage)
            ->subject('FUP pendiente de autorización')
            ->greeting('Hola '.$notifiable->name)
            ->line('Hay una FUP en espera de tu autorización.')
            ->line('Número: '.($fup?->fup_number ?? '—'))
            ->action('Revisar FUP', $url)
            ->line('Gracias.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $fup = $this->authorizationRequest->fup;
        $url = $fup
            ? CommercialFupPanelUrls::reviewAuthorization($fup, $this->authorizationRequest)
            : url('/admin');

        return FilamentNotification::make()
            ->title('Autorización de FUP')
            ->body('La FUP '.($fup?->fup_number ?? '').' requiere tu validación.')
            ->actions([
                Action::make('review')
                    ->label('Revisar')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }
}