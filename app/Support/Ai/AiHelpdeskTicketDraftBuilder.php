<?php

namespace App\Support\Ai;

use App\Enums\HelpdeskTicketOpenedByType;
use App\Enums\HelpdeskTicketSeverity;
use App\Enums\HelpdeskTicketStatus;
use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\CrmAccount;
use App\Models\HelpdeskCategory;
use App\Models\User;
use Illuminate\Support\Carbon;

class AiHelpdeskTicketDraftBuilder
{
    /**
     * @param  array<string, mixed>  $arguments
     * @return array{ok: true, payload: array<string, mixed>}|array{ok: false, content: string}
     */
    public function build(User $user, array $arguments): array
    {
        $missing = [];
        $subject = trim((string) ($arguments['subject'] ?? ''));

        if ($subject === '') {
            $missing[] = 'asunto del ticket';
        }

        $description = trim((string) ($arguments['description'] ?? ''));

        if ($description === '') {
            $missing[] = 'descripción';
        }

        $accountId = (int) ($arguments['crm_account_id'] ?? 0);
        $account = $accountId > 0 ? CrmAccount::query()->find($accountId) : null;

        if (! $account) {
            $missing[] = 'cuenta CRM (usa search_crm_accounts y confirma el ID)';
        }

        $categoryId = (int) ($arguments['helpdesk_category_id'] ?? 0);
        $category = $categoryId > 0 ? HelpdeskCategory::query()->where('is_active', true)->find($categoryId) : null;

        if (! $category) {
            $missing[] = 'categoría de helpdesk (usa search_helpdesk_categories)';
        }

        $contactId = (int) ($arguments['opened_by_contact_id'] ?? $arguments['crm_contact_id'] ?? 0);

        if ($account) {
            $accountContactCount = $account->contacts()->count();

            if ($accountContactCount > 0) {
                if ($contactId <= 0) {
                    $missing[] = 'contacto de la cuenta (usa search_crm_account_contacts)';
                } elseif (! $account->contacts()->whereKey($contactId)->exists()) {
                    return [
                        'ok' => false,
                        'content' => 'El contacto indicado no pertenece a esa cuenta CRM. Busca contactos con search_crm_account_contacts.',
                    ];
                }
            } elseif ($contactId > 0) {
                $contactId = 0;
            }
        }

        $companyId = (int) ($arguments['company_id'] ?? 0);
        $departmentId = (int) ($arguments['company_department_id'] ?? 0);
        $employeeId = isset($arguments['employee_id']) ? (int) $arguments['employee_id'] : null;

        if ($companyId <= 0 || $departmentId <= 0) {
            $employee = $user->helpdeskEmployee;

            if ($employee) {
                $companyId = (int) $employee->company_id;
                $departmentId = (int) $employee->company_department_id;

                if ($user->shouldAutoAssignHelpdeskTicketOnCreate()) {
                    $employeeId = (int) $employee->getKey();
                }
            }
        }

        if ($companyId <= 0 || ! Company::query()->whereKey($companyId)->exists()) {
            $missing[] = 'empresa interna asignada';
        }

        if ($departmentId <= 0 || ! CompanyDepartment::query()->where('company_id', $companyId)->whereKey($departmentId)->exists()) {
            $missing[] = 'departamento interno asignado';
        }

        if ($missing !== []) {
            return [
                'ok' => false,
                'content' => 'Aún no puedo preparar el ticket. Falta: '.implode(', ', $missing).'.',
            ];
        }

        $payload = [
            'crm_account_id' => $accountId,
            'subject' => $subject,
            'description' => $description,
            'opened_at' => Carbon::now()->toIso8601String(),
            'helpdesk_category_id' => $categoryId,
            'status' => HelpdeskTicketStatus::Abierto->value,
            'severity' => HelpdeskTicketSeverity::Normal->value,
            'opened_by_type' => HelpdeskTicketOpenedByType::User->value,
            'opened_by_user_id' => $user->getKey(),
            'opened_by_contact_id' => $contactId > 0 ? $contactId : null,
            'company_id' => $companyId,
            'company_department_id' => $departmentId,
            'employee_id' => $employeeId,
        ];

        return ['ok' => true, 'payload' => $payload];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalizeForCreate(array $payload): array
    {
        $openedAt = $payload['opened_at'] ?? null;

        return [
            'crm_account_id' => (int) ($payload['crm_account_id'] ?? 0),
            'subject' => (string) ($payload['subject'] ?? ''),
            'description' => (string) ($payload['description'] ?? ''),
            'opened_at' => filled($openedAt) ? Carbon::parse($openedAt) : now(),
            'helpdesk_category_id' => (int) ($payload['helpdesk_category_id'] ?? 0),
            'status' => $payload['status'] ?? HelpdeskTicketStatus::Abierto->value,
            'severity' => $payload['severity'] ?? HelpdeskTicketSeverity::Normal->value,
            'opened_by_type' => $payload['opened_by_type'] ?? HelpdeskTicketOpenedByType::User->value,
            'opened_by_user_id' => (int) ($payload['opened_by_user_id'] ?? auth()->id()),
            'opened_by_contact_id' => filled($payload['opened_by_contact_id'] ?? null)
                ? (int) $payload['opened_by_contact_id']
                : null,
            'company_id' => (int) ($payload['company_id'] ?? 0),
            'company_department_id' => (int) ($payload['company_department_id'] ?? 0),
            'employee_id' => filled($payload['employee_id'] ?? null) ? (int) $payload['employee_id'] : null,
            'created_by' => auth()->id(),
        ];
    }
}
