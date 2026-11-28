<?php

namespace App\Support\Ai;

use App\Enums\CommercialQuoteWorkflowStep;
use App\Enums\CrmOpportunityStage;
use App\Enums\CrmRecordScope;
use App\Enums\HelpdeskTicketStatus;
use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\CrmAccounts\CrmAccountResource;
use App\Filament\Resources\CrmCalendarEvents\CrmCalendarEventResource;
use App\Filament\Resources\CrmContacts\CrmContactResource;
use App\Filament\Resources\CrmOpportunities\CrmOpportunityResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\HelpdeskContracts\HelpdeskContractResource;
use App\Filament\Resources\HelpdeskCoverages\HelpdeskCoverageResource;
use App\Filament\Resources\HelpdeskServiceAgreements\HelpdeskServiceAgreementResource;
use App\Filament\Resources\HelpdeskTickets\HelpdeskTicketResource;
use App\Models\CommercialQuote;
use App\Models\Company;
use App\Models\CrmAccount;
use App\Models\CrmCalendarEvent;
use App\Models\CrmContact;
use App\Models\CrmOpportunity;
use App\Models\Employee;
use App\Models\HelpdeskCategory;
use App\Models\HelpdeskContract;
use App\Models\HelpdeskCoverage;
use App\Models\HelpdeskServiceAgreement;
use App\Models\HelpdeskTicket;
use App\Models\User;
use App\Support\CommercialQuoteTotalsCalculator;
use App\Support\CrmRecordAccess;
use App\Support\HelpdeskRecordAccess;
use Illuminate\Database\Eloquent\Builder;

class AiReadTools
{
    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchRhEmployees(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $companyId = filled($arguments['company_id'] ?? null) ? (int) $arguments['company_id'] : null;

        $builder = Employee::query()
            ->with(['company:id,legal_name,trade_name', 'department:id,name'])
            ->orderBy('first_name')
            ->orderBy('last_name');

        $this->scopeRhEmployeesQuery($builder, $user);

        if ($companyId !== null) {
            $builder->where('company_id', $companyId);
        }

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('second_last_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('employee_number', 'like', "%{$query}%");
            });
        }

        $employees = $builder
            ->limit($this->limit())
            ->get(['id', 'company_id', 'company_department_id', 'first_name', 'last_name', 'second_last_name', 'email', 'employee_number', 'status']);

        if ($employees->isEmpty()) {
            return ['content' => 'No se encontraron empleados.'];
        }

        $lines = $employees->map(function (Employee $employee): string {
            $editUrl = '—';

            if ($employee->company_id !== null) {
                $editUrl = EmployeeResource::getUrl('edit', [
                    'company' => $employee->company_id,
                    'record' => $employee->getKey(),
                ]);
            }

            return sprintf(
                '#%d %s | estado: %s | empresa: %s | departamento: %s | %s',
                $employee->id,
                $employee->fullName(),
                $employee->status?->getLabel() ?? '—',
                $employee->company?->trade_name ?: ($employee->company?->legal_name ?? '—'),
                $employee->department?->name ?? '—',
                $editUrl,
            );
        });

        return ['content' => "Empleados RH:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchRhCompanies(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        $builder = Company::query()
            ->orderBy('legal_name');

        $this->scopeRhCompaniesQuery($builder, $user);

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('legal_name', 'like', "%{$query}%")
                    ->orWhere('trade_name', 'like', "%{$query}%");
            });
        }

        $companies = $builder
            ->limit($this->limit())
            ->get(['id', 'legal_name', 'trade_name', 'is_active']);

        if ($companies->isEmpty()) {
            return ['content' => 'No se encontraron empresas.'];
        }

        $lines = $companies->map(fn (Company $company): string => sprintf(
            '#%d %s (%s) | %s',
            $company->id,
            $company->legal_name,
            $company->trade_name,
            CompanyResource::getUrl('edit', ['record' => $company]),
        ));

        return ['content' => "Empresas RH:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCrmAccounts(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        $builder = CrmAccount::query()
            ->orderBy('name');

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('legal_name', 'like', "%{$query}%");
            });
        }

        $accounts = $builder
            ->limit($this->limit())
            ->get(['id', 'name', 'legal_name', 'primary_email', 'phone']);

        if ($accounts->isEmpty()) {
            return ['content' => 'No se encontraron cuentas CRM.'];
        }

        $lines = $accounts->map(fn (CrmAccount $account): string => sprintf(
            '#%d %s | razón social: %s | %s',
            $account->id,
            $account->name,
            $account->legal_name ?? '—',
            CrmAccountResource::getUrl('edit', ['record' => $account]),
        ));

        return ['content' => "Cuentas CRM:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function getCrmAccount(User $user, array $arguments): array
    {
        $account = CrmAccount::query()->find((int) ($arguments['account_id'] ?? 0));

        if (! $account) {
            return ['content' => 'Cuenta CRM no encontrada.'];
        }

        $contacts = $account->contacts()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit($this->limit())
            ->get(['id', 'crm_account_id', 'first_name', 'last_name', 'second_last_name', 'email', 'phone']);

        $contactLines = $contacts->map(fn (CrmContact $contact): string => sprintf(
            '#%d %s (%s)',
            $contact->id,
            $contact->fullName(),
            $contact->email ?? 'sin correo',
        ));

        return ['content' => implode("\n", array_filter([
            sprintf('Cuenta #%d %s', $account->id, $account->name),
            'Razón social: '.($account->legal_name ?? '—'),
            'Correo: '.($account->primary_email ?? '—'),
            'Teléfono: '.($account->phone ?? '—'),
            'URL: '.CrmAccountResource::getUrl('edit', ['record' => $account]),
            $contactLines->isNotEmpty() ? "Contactos:\n".$contactLines->implode("\n") : 'Contactos: sin registros.',
        ]))];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCrmAccountContacts(User $user, array $arguments): array
    {
        $accountId = (int) ($arguments['crm_account_id'] ?? $arguments['account_id'] ?? 0);
        $query = trim((string) ($arguments['query'] ?? ''));
        $account = CrmAccount::query()->find($accountId);

        if (! $account) {
            return ['content' => 'Indica una cuenta CRM válida (crm_account_id).'];
        }

        $builder = $account->contacts()
            ->orderBy('first_name')
            ->orderBy('last_name');

        $this->filterContacts($builder, $query);

        $contacts = $builder
            ->limit($this->limit())
            ->get(['id', 'crm_account_id', 'first_name', 'last_name', 'second_last_name', 'email', 'phone']);

        if ($contacts->isEmpty()) {
            return ['content' => "La cuenta {$account->name} no tiene contactos registrados."];
        }

        $lines = $contacts->map(fn (CrmContact $contact): string => sprintf(
            '#%d %s | %s | %s',
            $contact->id,
            $contact->fullName(),
            $contact->email ?? 'sin correo',
            CrmContactResource::getUrl('edit', ['record' => $contact]),
        ));

        return ['content' => "Contactos de {$account->name}:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCrmContacts(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        $builder = CrmContact::query()
            ->with('account:id,name')
            ->orderBy('first_name')
            ->orderBy('last_name');

        $this->filterContacts($builder, $query);

        $contacts = $builder
            ->limit($this->limit())
            ->get(['id', 'crm_account_id', 'first_name', 'last_name', 'second_last_name', 'email', 'phone']);

        if ($contacts->isEmpty()) {
            return ['content' => 'No se encontraron contactos CRM.'];
        }

        $lines = $contacts->map(fn (CrmContact $contact): string => sprintf(
            '#%d %s | %s | cuenta: %s | %s',
            $contact->id,
            $contact->fullName(),
            $contact->email ?? 'sin correo',
            $contact->account?->name ?? '—',
            CrmContactResource::getUrl('edit', ['record' => $contact]),
        ));

        return ['content' => "Contactos CRM:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCrmOpportunities(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $stage = CrmOpportunityStage::tryFrom((string) ($arguments['stage'] ?? ''));

        $builder = CrmOpportunity::query()
            ->with(['account:id,name', 'assignee:id,name'])
            ->orderByDesc('id');

        if ($user->crm_record_scope === CrmRecordScope::Own && ! $user->isAdministrator()) {
            $builder->where('assigned_user_id', $user->getKey());
        }

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($stage !== null) {
            $builder->where('stage', $stage);
        }

        $opportunities = $builder
            ->limit($this->limit())
            ->get(['id', 'crm_account_id', 'assigned_user_id', 'name', 'stage', 'amount']);

        if ($opportunities->isEmpty()) {
            return ['content' => 'No se encontraron oportunidades CRM.'];
        }

        $lines = $opportunities->map(fn (CrmOpportunity $opportunity): string => sprintf(
            '#%d %s | cuenta: %s | etapa: %s | responsable: %s | %s',
            $opportunity->id,
            $opportunity->name,
            $opportunity->account?->name ?? '—',
            $opportunity->stage?->getLabel() ?? '—',
            $opportunity->assignee?->name ?? '—',
            CrmOpportunityResource::getUrl('edit', ['record' => $opportunity]),
        ));

        return ['content' => "Oportunidades CRM:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCrmCalendarEvents(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $daysAhead = max(0, (int) ($arguments['days_ahead'] ?? 30));
        $startsAt = now()->startOfDay();
        $endsAt = now()->addDays($daysAhead)->endOfDay();

        $builder = CrmCalendarEvent::query()
            ->with(['account:id,name', 'opportunity:id,name', 'creator:id,name'])
            ->whereBetween('starts_at', [$startsAt, $endsAt])
            ->orderBy('starts_at');

        CrmRecordAccess::scopeCalendarEventsQuery($builder, $user);

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        $events = $builder
            ->limit($this->limit())
            ->get(['id', 'title', 'starts_at', 'crm_account_id', 'crm_opportunity_id', 'created_by']);

        if ($events->isEmpty()) {
            return ['content' => 'No se encontraron eventos de calendario CRM.'];
        }

        $lines = $events->map(fn (CrmCalendarEvent $event): string => sprintf(
            '#%d %s | %s | cuenta: %s | oportunidad: %s | %s',
            $event->id,
            $event->title,
            $event->starts_at?->format('Y-m-d H:i') ?? '—',
            $event->account?->name ?? '—',
            $event->opportunity?->name ?? '—',
            CrmCalendarEventResource::getUrl('edit', ['record' => $event]),
        ));

        return ['content' => "Eventos CRM:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchCommercialQuotes(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $workflowStep = CommercialQuoteWorkflowStep::tryFrom((string) ($arguments['workflow_step'] ?? ''));
        $createdByUserId = filled($arguments['created_by_user_id'] ?? null) ? (int) $arguments['created_by_user_id'] : null;

        $builder = CommercialQuote::query()
            ->with('creator:id,name')
            ->orderByDesc('id');

        if ($query !== '' && $query !== '*') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('quote_number', 'like', "%{$query}%")
                    ->orWhere('project_description', 'like', "%{$query}%");
            });
        }

        if ($workflowStep !== null) {
            $builder->whereActiveWorkflowStep($workflowStep);
        }

        if ($createdByUserId !== null) {
            $builder->where('created_by_user_id', $createdByUserId);
        }

        $quotes = $builder
            ->limit($this->limit())
            ->get(['id', 'quote_number', 'project_description', 'created_by_user_id', 'authorization_status', 'workflow_step']);

        if ($quotes->isEmpty()) {
            return ['content' => 'No se encontraron cotizaciones comerciales.'];
        }

        $lines = $quotes->map(fn (CommercialQuote $quote): string => sprintf(
            '%s | flujo: %s | autorización: %s | creado por: %s | %s',
            $quote->quote_number,
            $quote->activeWorkflowStep()->getLabel(),
            $quote->authorization_status?->getLabel() ?? '—',
            $quote->creator?->name ?? '—',
            CommercialQuoteResource::getUrl('edit', ['record' => $quote]),
        ));

        return ['content' => "Cotizaciones comerciales:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function getCommercialQuoteSummary(User $user, array $arguments): array
    {
        $quote = CommercialQuote::query()->find((int) ($arguments['commercial_quote_id'] ?? 0));

        if (! $quote) {
            return ['content' => 'Cotización comercial no encontrada.'];
        }

        $totals = CommercialQuoteTotalsCalculator::forQuote($quote);

        return ['content' => sprintf(
            "Resumen de %s:\nResponsable: %s\nImporte cliente: $%s\nMargen: %s%%\nMonto de margen: $%s\nURL: %s",
            $quote->quote_number,
            $totals['responsible_name'],
            number_format((float) $totals['customer_amount'], 2, '.', ','),
            number_format((float) $totals['margin_percent'], 2, '.', ','),
            number_format((float) $totals['margin_amount'], 2, '.', ','),
            CommercialQuoteResource::getUrl('edit', ['record' => $quote]),
        )];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchHelpdeskTickets(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $status = HelpdeskTicketStatus::tryFrom((string) ($arguments['status'] ?? ''));
        $assignedUserId = filled($arguments['assigned_user_id'] ?? null) ? (int) $arguments['assigned_user_id'] : null;

        $builder = HelpdeskTicket::query()
            ->with(['assignee:id,first_name,last_name,second_last_name'])
            ->orderByDesc('id');

        HelpdeskRecordAccess::scopeTicketsQuery($builder, $user);

        if ($query !== '') {
            $builder->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('subject', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if ($status !== null) {
            $builder->where('status', $status);
        }

        if ($assignedUserId !== null) {
            $employeeId = User::query()->whereKey($assignedUserId)->value('helpdesk_employee_id');
            $builder->where('employee_id', $employeeId ?: 0);
        }

        $tickets = $builder
            ->limit($this->limit())
            ->get(['id', 'subject', 'status', 'employee_id']);

        if ($tickets->isEmpty()) {
            return ['content' => 'No se encontraron tickets helpdesk.'];
        }

        $lines = $tickets->map(fn (HelpdeskTicket $ticket): string => sprintf(
            '#%d %s | estado: %s | asignado a: %s | %s',
            $ticket->id,
            $ticket->subject,
            $ticket->status?->getLabel() ?? '—',
            $ticket->assignee?->fullName() ?: '—',
            HelpdeskTicketResource::getUrl('edit', ['record' => $ticket]),
        ));

        return ['content' => "Tickets helpdesk:\n".$lines->implode("\n")];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchHelpdeskContracts(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        return $this->searchHelpdeskCatalog(
            HelpdeskContract::query(),
            $query,
            'Contratos helpdesk',
            fn (HelpdeskContract $record): string => HelpdeskContractResource::getUrl('edit', ['record' => $record]),
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchHelpdeskCoverages(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        return $this->searchHelpdeskCatalog(
            HelpdeskCoverage::query(),
            $query,
            'Coberturas helpdesk',
            fn (HelpdeskCoverage $record): string => HelpdeskCoverageResource::getUrl('edit', ['record' => $record]),
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchHelpdeskServiceAgreements(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        return $this->searchHelpdeskCatalog(
            HelpdeskServiceAgreement::query(),
            $query,
            'Acuerdos de servicio helpdesk',
            fn (HelpdeskServiceAgreement $record): string => HelpdeskServiceAgreementResource::getUrl('edit', ['record' => $record]),
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{content: string}
     */
    public function searchHelpdeskCategories(User $user, array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));

        $builder = HelpdeskCategory::query()
            ->with(['parent.parent'])
            ->where('is_active', true);

        if ($query !== '') {
            $builder->where('name', 'like', "%{$query}%");
        }

        $categories = $builder
            ->orderBy('depth')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($this->limit())
            ->get();

        if ($categories->isEmpty()) {
            return ['content' => 'No se encontraron categorías.'];
        }

        $lines = $categories->map(fn (HelpdeskCategory $category): string => sprintf(
            '#%d %s',
            $category->id,
            $category->fullPath(),
        ));

        return ['content' => "Categorías helpdesk:\n".$lines->implode("\n")];
    }

    protected function limit(): int
    {
        return (int) config('ai-assistant.search_limit', 10);
    }

    /** @param  Builder<Employee>  $builder */
    protected function scopeRhEmployeesQuery(Builder $builder, User $user): void
    {
        if ($user->hasUnrestrictedRhCompanyAccess()) {
            return;
        }

        $builder->whereIn('company_id', $user->companies()->select('companies.id'));
    }

    /** @param  Builder<Company>  $builder */
    protected function scopeRhCompaniesQuery(Builder $builder, User $user): void
    {
        if ($user->hasUnrestrictedRhCompanyAccess()) {
            return;
        }

        $builder->whereIn('id', $user->companies()->select('companies.id'));
    }

    /** @param  Builder<CrmContact>  $builder */
    protected function filterContacts(Builder $builder, string $query): void
    {
        if ($query === '') {
            return;
        }

        $builder->where(function (Builder $builder) use ($query): void {
            $builder
                ->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('second_last_name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        });
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $builder
     * @param  callable(TModel): string  $urlResolver
     * @return array{content: string}
     */
    protected function searchHelpdeskCatalog(Builder $builder, string $query, string $title, callable $urlResolver): array
    {
        if ($query !== '') {
            $builder->where('name', 'like', "%{$query}%");
        }

        $records = $builder
            ->orderBy('name')
            ->limit($this->limit())
            ->get(['id', 'name']);

        if ($records->isEmpty()) {
            return ['content' => "No se encontraron registros para {$title}."];
        }

        $lines = $records->map(fn ($record): string => sprintf(
            '#%d %s | %s',
            $record->id,
            $record->name,
            $urlResolver($record),
        ));

        return ['content' => "{$title}:\n".$lines->implode("\n")];
    }
}
