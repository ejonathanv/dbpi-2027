<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Models\CommercialFupAuthorizationReview;
use App\Support\CommercialFupAuthorizationService;
use App\Support\CommercialFupCoverBuilder;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditCommercialFup extends EditRecord
{
    protected static string $resource = CommercialFupResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if (blank($record->cover_payload)) {
            $data['cover_payload'] = CommercialFupCoverBuilder::defaultPayload($record);

            return $data;
        }

        if (blank(data_get($record->cover_payload, 'billing.mub_percent'))) {
            $defaults = CommercialFupCoverBuilder::defaultPayload($record);
            data_set($data, 'cover_payload.billing.mub_percent', $defaults['billing']['mub_percent'] ?? '');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->isEditingLocked()) {
            return [];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }

    public function resendAuthorizationToReviewer(int $reviewId): void
    {
        $fup = $this->getRecord();

        Gate::authorize('update', $fup);

        $review = CommercialFupAuthorizationReview::query()
            ->whereKey($reviewId)
            ->whereHas(
                'request',
                fn ($query) => $query->where('commercial_fup_id', $fup->getKey()),
            )
            ->first();

        if (! $review instanceof CommercialFupAuthorizationReview) {
            Notification::make()
                ->title('No se encontró la revisión')
                ->danger()
                ->send();

            return;
        }

        try {
            CommercialFupAuthorizationService::resendReviewNotification($review);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo reenviar')
                ->body(collect($exception->errors())->flatten()->first() ?? 'Revise los datos.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Notificación reenviada')
            ->success()
            ->send();
    }
}
