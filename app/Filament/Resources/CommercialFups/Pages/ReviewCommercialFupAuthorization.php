<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Models\CommercialFupAuthorizationRequest;
use App\Models\CommercialFupAuthorizationReview;
use App\Models\User;
use App\Support\CommercialFupAuthorizationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReviewCommercialFupAuthorization extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CommercialFupResource::class;

    protected string $view = 'filament.resources.commercial-fups.authorization-review';

    public CommercialFupAuthorizationRequest $authorizationRequest;

    public ?string $approveNotes = null;

    public ?string $rejectNotes = null;

    public ?string $changesNotes = null;

    public function mount(int|string $record, CommercialFupAuthorizationRequest $authorizationRequest): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless($authorizationRequest->commercial_fup_id === $this->getRecord()->getKey(), 404);

        $this->authorizationRequest = $authorizationRequest->load(['reviews.user', 'requester']);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Autorización · '.$this->getRecord()->fup_number;
    }

    public function getReviewForCurrentUser(): ?CommercialFupAuthorizationReview
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

        abort_unless($review instanceof CommercialFupAuthorizationReview, 403);

        CommercialFupAuthorizationService::respond($review, $decision, $notes);

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
    public function getFrozenCoverProperty(): array
    {
        return (array) ($this->authorizationRequest->frozen_snapshot['cover'] ?? []);
    }
}