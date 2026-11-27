<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Filament\Resources\CommercialQuotes\Widgets\CommercialQuoteEditSummaryWidget;
use App\Models\CommercialQuoteAuthorizationReview;
use App\Models\CommercialQuoteAuthorizer;
use App\Models\User;
use App\Support\CommercialQuoteAuthorizationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditCommercialQuote extends EditRecord
{
    protected static string $resource = CommercialQuoteResource::class;

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        if ($this->getRecord()->isEditingLocked()) {
            $schema->disabled();
        }

        return $schema;
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            CommercialQuoteEditSummaryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetData(): array
    {
        return [
            'record' => $this->getRecord(),
        ];
    }

    protected function getHeaderActions(): array
    {
        $quote = $this->getRecord();

        return [
            Action::make('requestAuthorization')
                ->label('Solicitar autorización')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('warning')
                ->visible(fn (): bool => $quote->canRequestAuthorization())
                ->modalHeading('Solicitar autorización')
                ->modalDescription('La cotización quedará bloqueada con los importes actuales hasta que los autorizadores respondan.')
                ->form([
                    CheckboxList::make('reviewer_ids')
                        ->label('Autorizadores')
                        ->options(function (): array {
                            return CommercialQuoteAuthorizer::query()
                                ->where('is_active', true)
                                ->with('user')
                                ->get()
                                ->mapWithKeys(fn (CommercialQuoteAuthorizer $authorizer): array => [
                                    (string) $authorizer->user_id => (string) ($authorizer->user?->name ?? 'Usuario'),
                                ])
                                ->all();
                        })
                        ->default(fn (): array => CommercialQuoteAuthorizer::query()
                            ->where('is_active', true)
                            ->pluck('user_id')
                            ->map(fn (int $id): string => (string) $id)
                            ->all())
                        ->required()
                        ->minItems(1)
                        ->columns(1),
                ])
                ->action(function (array $data): void {
                    $reviewerIds = array_map('intval', $data['reviewer_ids'] ?? []);
                    $user = Filament::auth()->user();

                    if (! $user instanceof User) {
                        return;
                    }

                    try {
                        CommercialQuoteAuthorizationService::request($this->getRecord(), $user, $reviewerIds);
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('No se pudo solicitar autorización')
                            ->body(collect($exception->errors())->flatten()->first() ?? 'Revise los datos.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->getRecord()->refresh();

                    Notification::make()
                        ->title('Autorización solicitada')
                        ->success()
                        ->send();

                    $this->dispatch('commercial-quote-margin-updated');
                }),
            DeleteAction::make()
                ->visible(fn (): bool => ! $quote->isEditingLocked()),
            ForceDeleteAction::make()
                ->visible(fn (): bool => ! $quote->isEditingLocked()),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->dispatch('commercial-quote-margin-updated');
    }

    public function resendAuthorizationToReviewer(int $reviewId): void
    {
        $quote = $this->getRecord();

        Gate::authorize('update', $quote);

        $review = CommercialQuoteAuthorizationReview::query()
            ->whereKey($reviewId)
            ->whereHas(
                'request',
                fn ($query) => $query->where('commercial_quote_id', $quote->getKey()),
            )
            ->first();

        if (! $review instanceof CommercialQuoteAuthorizationReview) {
            Notification::make()
                ->title('No se encontró la revisión')
                ->danger()
                ->send();

            return;
        }

        try {
            CommercialQuoteAuthorizationService::resendReviewNotification($review);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('No se pudo reenviar')
                ->body(collect($exception->errors())->flatten()->first() ?? 'Revise los datos.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Solicitud reenviada')
            ->body('Se notificó de nuevo a '.$review->user?->name.'.')
            ->success()
            ->send();
    }
}
