<?php

namespace App\Support;

use App\Enums\CommercialQuoteAuthorizationRequestStatus;
use App\Enums\CommercialQuoteAuthorizationReviewStatus;
use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Models\CommercialFup;
use App\Models\CommercialFupAuthorizationRequest;
use App\Models\CommercialFupAuthorizationReview;
use App\Models\CommercialQuoteAuthorizer;
use App\Models\User;
use App\Notifications\CommercialFupAuthorizationRequestedNotification;
use App\Notifications\CommercialFupAuthorizationRespondedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommercialFupAuthorizationService
{
    /**
     * @param  list<int>  $reviewerUserIds
     */
    public static function request(CommercialFup $fup, User $requester, array $reviewerUserIds): CommercialFupAuthorizationRequest
    {
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

        $snapshot = CommercialFupCoverBuilder::capture($fup);

        return DB::transaction(function () use ($fup, $requester, $reviewerUserIds, $snapshot): CommercialFupAuthorizationRequest {
            $fup->update([
                'authorization_status' => CommercialQuoteAuthorizationStatus::PendingAuthorization,
                'frozen_snapshot' => $snapshot,
                'authorization_locked_at' => now(),
            ]);

            $authorizationRequest = CommercialFupAuthorizationRequest::query()->create([
                'commercial_fup_id' => $fup->getKey(),
                'requested_by_user_id' => $requester->getKey(),
                'status' => CommercialQuoteAuthorizationRequestStatus::Pending,
                'frozen_snapshot' => $snapshot,
                'requested_at' => now(),
            ]);

            foreach ($reviewerUserIds as $userId) {
                $review = CommercialFupAuthorizationReview::query()->create([
                    'commercial_fup_authorization_request_id' => $authorizationRequest->getKey(),
                    'user_id' => $userId,
                    'status' => CommercialQuoteAuthorizationReviewStatus::Pending,
                ]);

                $reviewer = User::query()->find($userId);

                if ($reviewer instanceof User) {
                    $reviewer->notify(new CommercialFupAuthorizationRequestedNotification($authorizationRequest, $review));
                }
            }

            return $authorizationRequest;
        });
    }

    public static function respond(
        CommercialFupAuthorizationReview $review,
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

            $request = $review->request()->with('reviews')->firstOrFail();
            $fup = $request->fup()->firstOrFail();

            if ($decision === CommercialQuoteAuthorizationReviewStatus::Rejected) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::Rejected]);
                $fup->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::Rejected,
                    'frozen_snapshot' => null,
                    'authorization_locked_at' => null,
                ]);

                return;
            }

            if ($decision === CommercialQuoteAuthorizationReviewStatus::ChangesSuggested) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::ChangesSuggested]);
                $fup->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::ChangesRequested,
                    'frozen_snapshot' => null,
                    'authorization_locked_at' => null,
                ]);

                return;
            }

            $reviews = $request->reviews;
            $pending = $reviews->where('status', CommercialQuoteAuthorizationReviewStatus::Pending)->count();

            if ($pending === 0 && $reviews->every(
                fn (CommercialFupAuthorizationReview $item): bool => $item->status === CommercialQuoteAuthorizationReviewStatus::Approved,
            )) {
                $request->update(['status' => CommercialQuoteAuthorizationRequestStatus::Approved]);
                $fup->update([
                    'authorization_status' => CommercialQuoteAuthorizationStatus::Authorized,
                ]);
            }
        });

        $review->refresh()->loadMissing(['user', 'request.fup', 'request.requester']);

        self::notifyStakeholdersOfReviewResponse($review);
    }

    protected static function notifyStakeholdersOfReviewResponse(CommercialFupAuthorizationReview $review): void
    {
        $request = $review->request;
        $fup = $request?->fup;

        if (! $request || ! $fup) {
            return;
        }

        $recipientIds = array_values(array_unique(array_filter([
            $request->requested_by_user_id,
            $fup->created_by_user_id,
        ])));

        if ($recipientIds === []) {
            return;
        }

        $reviewerId = $review->user_id;

        User::query()
            ->whereIn('id', $recipientIds)
            ->get()
            ->each(function (User $recipient) use ($review, $reviewerId): void {
                if ($recipient->getKey() === $reviewerId) {
                    return;
                }

                $recipient->notify(new CommercialFupAuthorizationRespondedNotification($review));
            });
    }
}