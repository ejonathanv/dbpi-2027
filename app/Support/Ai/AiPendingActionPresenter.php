<?php

namespace App\Support\Ai;

use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\CrmAccount;
use App\Models\CrmContact;
use App\Models\CrmOpportunity;
use App\Models\HelpdeskCategory;
use Illuminate\Support\Str;

class AiPendingActionPresenter
{
    /**
     * @param  array<string, mixed>  $pendingAction
     */
    public static function assistantMessage(array $pendingAction): string
    {
        $lines = self::summaryLines($pendingAction);
        $intro = match ($pendingAction['type'] ?? '') {
            'create_helpdesk_ticket' => 'Dejé preparado un ticket de soporte con estos datos:',
            'create_crm_account' => 'Dejé preparada una cuenta CRM con estos datos:',
            'create_crm_opportunity' => 'Dejé preparada una oportunidad con estos datos:',
            'create_commercial_quote' => 'Dejé preparada una cotización en borrador con estos datos:',
            default => 'Dejé preparado un borrador con estos datos:',
        };

        return $intro."\n".implode("\n", array_map(
            fn (string $line): string => '• '.$line,
            $lines,
        ))."\n\nSi está correcto, confírmalo abajo; si no, dime qué ajustar.";
    }

    /**
     * @param  array<string, mixed>  $pendingAction
     * @return array<int, string>
     */
    public static function summaryLines(array $pendingAction): array
    {
        $type = (string) ($pendingAction['type'] ?? '');
        $payload = (array) ($pendingAction['payload'] ?? []);

        return match ($type) {
            'create_helpdesk_ticket' => array_values(array_filter([
                'Cuenta: '.self::accountLabel((int) ($payload['crm_account_id'] ?? 0)),
                filled($payload['opened_by_contact_id'] ?? null)
                    ? 'Contacto: '.self::contactLabel((int) $payload['opened_by_contact_id'])
                    : null,
                'Categoría: '.self::categoryLabel((int) ($payload['helpdesk_category_id'] ?? 0)),
                'Asunto: '.(string) ($payload['subject'] ?? '—'),
                'Descripción: '.Str::limit((string) ($payload['description'] ?? '—'), 280),
                filled($payload['company_id'] ?? null)
                    ? 'Empresa: '.self::companyLabel((int) $payload['company_id'])
                    : null,
                filled($payload['company_department_id'] ?? null)
                    ? 'Departamento: '.self::departmentLabel((int) $payload['company_department_id'])
                    : null,
            ])),
            'create_crm_account' => array_filter([
                'Nombre: '.(string) ($payload['name'] ?? '—'),
                filled($payload['legal_name'] ?? null) ? 'Razón social: '.$payload['legal_name'] : null,
                filled($payload['primary_email'] ?? null) ? 'Correo: '.$payload['primary_email'] : null,
                filled($payload['phone'] ?? null) ? 'Teléfono: '.$payload['phone'] : null,
            ]),
            'create_crm_opportunity' => array_values(array_filter([
                'Oportunidad: '.(string) ($payload['name'] ?? '—'),
                'Cuenta: '.self::accountLabel((int) ($payload['crm_account_id'] ?? 0)),
                filled($payload['description'] ?? null) ? 'Descripción: '.Str::limit((string) $payload['description'], 200) : null,
                filled($payload['amount'] ?? null) ? 'Monto: '.(string) $payload['amount'] : null,
            ])),
            'create_commercial_quote' => [
                'Oportunidad: '.self::opportunityLabel((int) ($payload['crm_opportunity_id'] ?? 0)),
                'Proyecto: '.Str::limit((string) ($payload['project_description'] ?? '—'), 200),
            ],
            default => ['Acción: '.(string) ($pendingAction['label'] ?? 'Crear registro')],
        };
    }

    protected static function accountLabel(int $accountId): string
    {
        if ($accountId <= 0) {
            return '—';
        }

        return CrmAccount::query()->find($accountId)?->name ?? "cuenta #{$accountId}";
    }

    protected static function opportunityLabel(int $opportunityId): string
    {
        if ($opportunityId <= 0) {
            return '—';
        }

        $opportunity = CrmOpportunity::query()->find($opportunityId);

        return $opportunity?->name ?? "oportunidad #{$opportunityId}";
    }

    protected static function contactLabel(int $contactId): string
    {
        if ($contactId <= 0) {
            return '—';
        }

        return CrmContact::query()->find($contactId)?->name ?? "contacto #{$contactId}";
    }

    protected static function categoryLabel(int $categoryId): string
    {
        if ($categoryId <= 0) {
            return '—';
        }

        $category = HelpdeskCategory::query()->with(['parent.parent'])->find($categoryId);

        return $category?->fullPath() ?? "categoría #{$categoryId}";
    }

    protected static function companyLabel(int $companyId): string
    {
        if ($companyId <= 0) {
            return '—';
        }

        return Company::query()->find($companyId)?->legal_name ?? "empresa #{$companyId}";
    }

    protected static function departmentLabel(int $departmentId): string
    {
        if ($departmentId <= 0) {
            return '—';
        }

        return CompanyDepartment::query()->find($departmentId)?->name ?? "departamento #{$departmentId}";
    }
}
