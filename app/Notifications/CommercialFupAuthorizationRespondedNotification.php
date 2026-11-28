<?php

namespace App\Notifications;

use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Models\CommercialFupAuthorizationReview;
use App\Support\CommercialFupPanelUrls;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommercialFupAuthorizationRespondedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommercialFupAuthorizationReview $review,
    ) {
        $this->review->loadMissing(['user', 'request.fup', 'request.requester']);
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
        $fup = $this->review->request?->fup;
        $reviewerName = $this->review->user?->name ?? 'Un autorizador';
        $url = $this->editAuthorizationTabUrl();

        $message = (new MailMessage)
            ->subject($this->notificationTitle().(filled($fup?->fup_number) ? ' · '.$fup->fup_number : ''))
            ->greeting('Hola '.$notifiable->name)
            ->line($this->summaryLine($reviewerName, $fup?->fup_number));

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
        $fup = $this->review->request?->fup;
        $reviewerName = $this->review->user?->name ?? 'Un autorizador';
        $url = $this->editAuthorizationTabUrl();

        return FilamentNotification::make()
            ->title($this->notificationTitle())
            ->body($this->summaryLine($reviewerName, $fup?->fup_number))
            ->actions([
                Action::make('open')
                    ->label('Ver autorizaciones')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }

    protected function editAuthorizationTabUrl(): string
    {
        $fup = $this->review->request?->fup;

        if (! $fup) {
            return Filament::getPanel('admin')->getUrl();
        }

        return CommercialFupPanelUrls::editAuthorizationTab($fup);
    }

    protected function notificationTitle(): string
    {
        return match ($this->review->status) {
            CommercialQuoteAuthorizationReviewStatus::Approved => 'FUP autorizada',
            CommercialQuoteAuthorizationReviewStatus::Rejected => 'FUP rechazada',
            CommercialQuoteAuthorizationReviewStatus::ChangesSuggested => 'Cambios sugeridos en FUP',
            default => 'Respuesta de autorización de FUP',
        };
    }

    protected function summaryLine(string $reviewerName, ?string $fupNumber): string
    {
        $number = filled($fupNumber) ? $fupNumber : 'sin número';

        return match ($this->review->status) {
            CommercialQuoteAuthorizationReviewStatus::Approved => "{$reviewerName} autorizó la FUP {$number}.",
            CommercialQuoteAuthorizationReviewStatus::Rejected => "{$reviewerName} rechazó la FUP {$number}.",
            CommercialQuoteAuthorizationReviewStatus::ChangesSuggested => "{$reviewerName} sugirió modificaciones en la FUP {$number}.",
            default => "{$reviewerName} respondió la autorización de la FUP {$number}.",
        };
    }
}