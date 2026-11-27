<?php

namespace App\Support;

use App\Enums\CommercialQuoteAuthorizationRequestStatus;
use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Models\CommercialQuote;
use App\Models\CommercialQuoteAuthorizationRequest;
use App\Models\CommercialQuoteAuthorizationReview;
use App\Models\CommercialQuoteAuthorizer;
use App\Models\User;
use App\Notifications\CommercialQuoteAuthorizationRequestedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommercialQuoteAuthorizationService
{
    /**
     * @param  list<int>  $reviewerUserIds
     */
    public static function request(CommercialQuote $quote, User $requester, array $reviewerUserIds): CommercialQuoteAuthorizationRequest
    {
        if (! $quote->canRequestAuthorization()) {
            throw ValidationException::withMessages([
                'authorization' => 'La cotización no puede enviarse a autorización en su estado actual.',
            ]);
        }

        $reviewerUserIds = array_values(array_unique(array_map('intval', $reviewerUserIds)));

        if ($reviewerUserIds === []) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Seleccione al menos un autorizador.',
            ]);
        }

        $activeAuthorizerIds = CommercialQuoteAuthorizer::query()
            ->where('is_active', true)
            ->whereIn('user_id', $reviewerUserIds)
            ->pluck('user_id')
            ->all();

        if (count($activeAuthorizerIds) !== count($reviewerUserIds)) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Uno o más autorizadores no están activos en el catálogo.',
            ]);
        }

        $snapshot = CommercialQuoteFreezeSupport::capture($quote);

        return DB::transaction(function () use ($quote, $requester, $reviewerUserIds, $snapshot): CommercialQuoteAuthorizationRequest {
            $quote->update([
                'authorization_status' => CommercialQuoteAuthorizationStatus::PendingAuthorization,
                'frozen_snapshot' => $snapshot,
                'authorization_locked_at' => now(),
            ]);

            $authorizationRequest = CommercialQuoteAuthorizationRequest::query()->create([
                'commercial_quote_id' => $quote->getKey(),
                'requested_by_user_id' => $requester->getKey(),
                'status' => CommercialQuoteAuthorizationRequestStatus::Pending,
                'frozen_snapshot' => $snapshot,
                'requested_at' => now(),
            ]);

            foreach ($reviewerUserIds as $userId) {
                $review = CommercialQuoteAuthorizationReview::query()->create([
                    'commercial_quote_authorization_request_id' => $authorizationRequest->getKey(),
                    'user_id' => $userId,
                    'status' => CommercialQuoteAuthorizationReviewStatus::Pending,
                ]);

                $reviewer = User::query()->find($userId);

                if ($reviewer instanceof User) {
                    $reviewer->notify(new CommercialQuoteAuthorizationRequestedNotification($authorizationRequest, $review));
                }
            }

            return $authorizationRequest;
        });
    }

    public static function respond(
        CommercialQuoteAuthorizationReview $review,
        CommercialQuoteAuthorizationReviewStatus $decision,
        ?string $notes,
    ): void {
        if ($review->status !== CommercialQuoteAuthorizationReviewStatus::Pending) {
            throw ValidationException::withMessages([
                'review' => 'Esta revisión ya fue respondida.',
            ]);
        }

        DB::transaction(function () use ($review, $decision, $notes): void {
            $review->update([
                'status' => $decision,
                'notes' => filled($notes) ? trim($notes) : null,
                'responded_at' => now(),
            ]);

            $request = $review->request()
                ->with('reviews')
                ->firstOrFail();

            $quote = $request->quote()->firstOrFail();

            if ($decision === CommercialQuoteAuthorizationReviewStatus::Rejected) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::Rejected]);
                $quote->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::Rejected,
                    'frozen_snapshot' => null,
                    'authorization_locked_at' => null,
                ]);

                return;
            }

            if ($decision === CommercialQuoteAuthorizationReviewStatus::ChangesSuggested) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::ChangesSuggested]);
                $quote->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::ChangesRequested,
                    'frozen_snapshot' => null,
                    'authorization_locked_at' => null,
                ]);

                return;
            }

            $reviews = $request->reviews;
            $pending = $reviews->where('status', CommercialQuoteAuthorizationReviewStatus::Pending)->count();
            $rejected = $reviews->where('status', CommercialQuoteAuthorizationReviewStatus::Rejected)->count();
            $changes = $reviews->where('status', CommercialQuoteAuthorizationReviewStatus::ChangesSuggested)->count();

            if ($rejected > 0) {
                return;
            }

            if ($changes > 0) {
                return;
            }

            if ($pending === 0 && $reviews->every(
                fn (CommercialQuoteAuthorizationReview $item): bool => $item->status === CommercialQuoteAuthorizationReviewStatus::Approved,
            )) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::Approved]);
                $quote->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::Authorized,
                ]);
            }
        });
    }
}
