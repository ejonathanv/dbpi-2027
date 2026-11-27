<?php

namespace App\Notifications;

use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Models\CommercialQuoteAuthorizationReview;
use App\Support\CommercialQuotePanelUrls;
use Filament\Facades\Filament;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommercialQuoteAuthorizationRespondedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommercialQuoteAuthorizationReview $review,
    ) {
        $this->review->loadMissing(['user', 'request.quote', 'request.requester']);
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
        $quote = $this->review->request?->quote;
        $reviewerName = $this->review->user?->name ?? 'Un autorizador';
        $url = $this->editAuthorizationTabUrl();

        $message = (new MailMessage)
            ->subject($this->notificationTitle().(filled($quote?->quote_number) ? ' · '.$quote->quote_number : ''))
            ->greeting('Hola '.$notifiable->name)
            ->line($this->summaryLine($reviewerName, $quote?->quote_number));

        if (filled($this->review->notes)) {
            $message->line('Notas: '.$this->review->notes);
        }

        return $message
            ->action('Ver autorizaciones', $url)
            ->line('Gracias.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $quote = $this->review->request?->quote;
        $reviewerName = $this->review->user?->name ?? 'Un autorizador';
        $url = $this->editAuthorizationTabUrl();

        return FilamentNotification::make()
            ->title($this->notificationTitle())
            ->body($this->summaryLine($reviewerName, $quote?->quote_number))
            ->actions([
                Action::make('open')
                    ->label('Ver autorizaciones')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }

    protected function editAuthorizationTabUrl(): string
    {
        $quote = $this->review->request?->quote;

        if (! $quote) {
            return Filament::getPanel('admin')->getUrl();
        }

        return CommercialQuotePanelUrls::editAuthorizationTab($quote);
    }

    protected function notificationTitle(): string
    {
        return match ($this->review->status) {
            CommercialQuoteAuthorizationReviewStatus::Approved => 'Cotización autorizada',
            CommercialQuoteAuthorizationReviewStatus::Rejected => 'Cotización rechazada',
            CommercialQuoteAuthorizationReviewStatus::ChangesSuggested => 'Cambios sugeridos en cotización',
            default => 'Respuesta de autorización',
        };
    }

    protected function summaryLine(string $reviewerName, ?string $quoteNumber): string
    {
        $number = filled($quoteNumber) ? $quoteNumber : 'sin número';

        return match ($this->review->status) {
            CommercialQuoteAuthorizationReviewStatus::Approved => "{$reviewerName} autorizó la cotización {$number}.",
            CommercialQuoteAuthorizationReviewStatus::Rejected => "{$reviewerName} rechazó la cotización {$number}.",
            CommercialQuoteAuthorizationReviewStatus::ChangesSuggested => "{$reviewerName} sugirió modificaciones en la cotización {$number}.",
            default => "{$reviewerName} respondió la autorización de la cotización {$number}.",
        };
    }
}