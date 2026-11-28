<?php

namespace App\Support\Ai;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AiToolExecutor
{
    public function __construct(
        protected AiUserContextBuilder $contextBuilder,
        protected AiReadTools $readTools,
    ) {}

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function execute(User $user, string $name, array $arguments): array
    {
        if (! in_array($name, $this->contextBuilder->enabledToolNames($user), true)) {
            throw new AuthorizationException("Herramienta no permitida: {$name}");
        }

        return match ($name) {
            'search_rh_employees' => $this->readTools->searchRhEmployees($user, $arguments),
            'search_rh_companies' => $this->readTools->searchRhCompanies($user, $arguments),
            'search_crm_accounts' => $this->readTools->searchCrmAccounts($user, $arguments),
            'get_crm_account' => $this->readTools->getCrmAccount($user, $arguments),
            'search_crm_account_contacts' => $this->readTools->searchCrmAccountContacts($user, $arguments),
            'search_crm_contacts' => $this->readTools->searchCrmContacts($user, $arguments),
            'search_crm_opportunities' => $this->readTools->searchCrmOpportunities($user, $arguments),
            'search_crm_calendar_events' => $this->readTools->searchCrmCalendarEvents($user, $arguments),
            'search_commercial_quotes' => $this->readTools->searchCommercialQuotes($user, $arguments),
            'get_commercial_quote_summary' => $this->readTools->getCommercialQuoteSummary($user, $arguments),
            'search_helpdesk_tickets' => $this->readTools->searchHelpdeskTickets($user, $arguments),
            'search_helpdesk_contracts' => $this->readTools->searchHelpdeskContracts($user, $arguments),
            'search_helpdesk_coverages' => $this->readTools->searchHelpdeskCoverages($user, $arguments),
            'search_helpdesk_service_agreements' => $this->readTools->searchHelpdeskServiceAgreements($user, $arguments),
            'search_helpdesk_categories' => $this->readTools->searchHelpdeskCategories($user, $arguments),
            default => ['content' => 'Herramienta desconocida.'],
        };
    }
}
