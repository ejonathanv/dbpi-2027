<?php

namespace App\Support\Ai;

use App\Enums\SalaryCurrency;
use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Filament\Resources\CrmAccounts\CrmAccountResource;
use App\Filament\Resources\CrmOpportunities\CrmOpportunityResource;
use App\Filament\Resources\HelpdeskTickets\HelpdeskTicketResource;
use App\Models\CommercialQuote;
use App\Models\CrmAccount;
use App\Models\CrmOpportunity;
use App\Models\HelpdeskTicket;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AiCreateRecordActions
{
    /**
     * @param  array<string, mixed>  $pendingAction
     * @return array{message: string, edit_url: string}
     */
    public function execute(User $user, array $pendingAction): array
    {
        $type = (string) ($pendingAction['type'] ?? '');
        $payload = (array) ($pendingAction['payload'] ?? []);

        return match ($type) {
            'create_crm_account' => $this->createCrmAccount($user, $payload),
            'create_crm_opportunity' => $this->createCrmOpportunity($user, $payload),
            'create_commercial_quote' => $this->createCommercialQuote($user, $payload),
            'create_helpdesk_ticket' => $this->createHelpdeskTicket($user, $payload),
            default => throw ValidationException::withMessages([
                'action' => 'Acción no reconocida.',
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{message: string, edit_url: string}
     */
    protected function createCrmAccount(User $user, array $payload): array
    {
        if (blank($payload['name'] ?? null)) {
            throw ValidationException::withMessages(['name' => 'El nombre es obligatorio.']);
        }

        $account = CrmAccount::query()->create([
            'name' => $payload['name'],
            'legal_name' => $payload['legal_name'] ?? $payload['name'],
            'primary_email' => $payload['primary_email'] ?? null,
            'phone' => $payload['phone'] ?? null,
        ]);

        return [
            'message' => "Cuenta «{$account->name}» creada (#{$account->id}).",
            'edit_url' => CrmAccountResource::getUrl('edit', ['record' => $account]),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{message: string, edit_url: string}
     */
    protected function createCrmOpportunity(User $user, array $payload): array
    {
        if (blank($payload['name'] ?? null) || blank($payload['crm_account_id'] ?? null)) {
            throw ValidationException::withMessages(['payload' => 'Nombre y cuenta son obligatorios.']);
        }

        $opportunity = CrmOpportunity::query()->create([
            'name' => $payload['name'],
            'crm_account_id' => $payload['crm_account_id'],
            'description' => $payload['description'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'assigned_user_id' => $payload['assigned_user_id'] ?? $user->getKey(),
            'stage' => $payload['stage'] ?? null,
        ]);

        return [
            'message' => "Oportunidad «{$opportunity->name}» creada (#{$opportunity->id}).",
            'edit_url' => CrmOpportunityResource::getUrl('edit', ['record' => $opportunity]),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{message: string, edit_url: string}
     */
    protected function createCommercialQuote(User $user, array $payload): array
    {
        if (blank($payload['crm_opportunity_id'] ?? null)) {
            throw ValidationException::withMessages(['crm_opportunity_id' => 'Se requiere oportunidad.']);
        }

        $quote = CommercialQuote::query()->create([
            'quote_number' => CommercialQuote::generateQuoteNumber(),
            'crm_opportunity_id' => $payload['crm_opportunity_id'],
            'crm_account_id' => $payload['crm_account_id'] ?? null,
            'crm_contact_id' => $payload['crm_contact_id'] ?? null,
            'project_description' => $payload['project_description'] ?? null,
            'quoted_at' => Carbon::today(),
            'presentation_currency' => SalaryCurrency::Mxn->value,
            'exchange_rate' => SystemSetting::getDecimal('exchange_rate_usd_mxn', 18),
            'validity_days' => 30,
            'created_by_user_id' => $user->getKey(),
        ]);

        return [
            'message' => "Cotización {$quote->quote_number} creada.",
            'edit_url' => CommercialQuoteResource::getUrl('edit', ['record' => $quote]),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{message: string, edit_url: string}
     */
    protected function createHelpdeskTicket(User $user, array $payload): array
    {
        if (! $user->canCreateHelpdeskTickets()) {
            throw new AuthorizationException('No puede crear tickets.');
        }

        $builder = app(AiHelpdeskTicketDraftBuilder::class);
        $draft = $builder->build($user, $payload);

        if ($draft['ok'] === false) {
            throw ValidationException::withMessages([
                'ticket' => $draft['content'],
            ]);
        }

        $attributes = $builder->normalizeForCreate($draft['payload']);
        $attributes['created_by'] = $user->getKey();

        $ticket = HelpdeskTicket::query()->create($attributes);

        return [
            'message' => "Ticket #{$ticket->id} «{$ticket->subject}» creado.",
            'edit_url' => HelpdeskTicketResource::getUrl('edit', ['record' => $ticket]),
        ];
    }
}
