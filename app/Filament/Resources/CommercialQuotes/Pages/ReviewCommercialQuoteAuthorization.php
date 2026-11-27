<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Models\CommercialQuoteAuthorizationRequest;
use App\Models\CommercialQuoteAuthorizationReview;
use App\Models\User;
use App\Support\CommercialQuoteAuthorizationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReviewCommercialQuoteAuthorization extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CommercialQuoteResource::class;

    protected string $view = 'filament.resources.commercial-quotes.authorization-review';

    public CommercialQuoteAuthorizationRequest $authorizationRequest;

    public ?string $approveNotes = null;

    public ?string $rejectNotes = null;

    public ?string $changesNotes = null;

    public function mount(int|string $record, CommercialQuoteAuthorizationRequest $authorizationRequest): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless($authorizationRequest->commercial_quote_id === $this->getRecord()->getKey(), 404);

        $this->authorizationRequest = $authorizationRequest->load(['reviews.user', 'requester']);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Autorización · '.$this->getRecord()->quote_number;
    }

    public function getReviewForCurrentUser(): ?CommercialQuoteAuthorizationReview
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return $this->authorizationRequest->reviews
            ->firstWhere('user_id', $user->getKey());
    }

    public function approve(): void
    {
        $this->submitReview(CommercialQuoteAuthorizationReviewStatus::Approved, $this->approveNotes);
    }

    public function reject(): void
    {
        $this->submitReview(CommercialQuoteAuthorizationReviewStatus::Rejected, $this->rejectNotes);
    }

    public function suggestChanges(): void
    {
        $this->submitReview(CommercialQuoteAuthorizationReviewStatus::ChangesSuggested, $this->changesNotes);
    }

    protected function submitReview(CommercialQuoteAuthorizationReviewStatus $decision, ?string $notes): void
    {
        $review = $this->getReviewForCurrentUser();

        abort_unless($review instanceof CommercialQuoteAuthorizationReview, 403);

        CommercialQuoteAuthorizationService::respond($review, $decision, $notes);

        Notification::make()
            ->title('Respuesta registrada')
            ->success()
            ->send();

        $this->authorizationRequest->refresh();
        $this->getRecord()->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFrozenPreviewProperty(): array
    {
        return (array) ($this->authorizationRequest->frozen_snapshot['preview'] ?? []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFrozenMarginLinesProperty(): array
    {
        return (array) ($this->authorizationRequest->frozen_snapshot['margin_lines'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFrozenTotalsProperty(): array
    {
        return (array) ($this->authorizationRequest->frozen_snapshot['totals'] ?? []);
    }
}
