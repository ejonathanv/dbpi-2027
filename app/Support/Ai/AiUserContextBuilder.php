<?php

namespace App\Support\Ai;

use App\Enums\AppModule;
use App\Enums\CrmRecordScope;
use App\Models\User;
use App\Support\ModuleAccess;

class AiUserContextBuilder
{
    public function systemPrompt(User $user): string
    {
        $modules = $this->moduleSummary($user);
        $constraints = $this->constraintSummary($user);
        $limit = config('ai-assistant.search_limit', 10);

        return <<<PROMPT
Eres el asistente de consultas de DBPI. Respondes en español, claro y conversacional.

Alcance:
- Solo consultas (lectura). No puedes crear, editar ni eliminar registros.
- Usa las herramientas search_* y get_* para obtener datos reales; nunca inventes registros ni IDs.
- Devuelve como máximo {$limit} registros por consulta; si hay más, indícalo y sugiere filtrar.

Módulos consultables:
- RH: empleados y empresas internas.
- Helpdesk: tickets (por texto, etapa o responsable), contratos, coberturas y acuerdos de servicio.
- Comercial: cotizaciones (por número, etapa, autor o margen con get_commercial_quote_summary).
- CRM: cuentas, contactos, oportunidades y eventos de calendario.

Reglas:
- Mantén el hilo: no repitas preguntas ya respondidas.
- Resume para el usuario en prosa; sin JSON ni bloques de código en tus respuestas.
- Cita enlaces de edición que devuelvan las herramientas cuando ayuden al usuario.
- Si no tienes permiso o no hay datos, dilo en una frase.

Permisos del usuario:
{$modules}

Restricciones:
{$constraints}
PROMPT;
    }

    /**
     * @return array<int, string>
     */
    public function enabledToolNames(User $user): array
    {
        $names = [];

        if (ModuleAccess::canViewModule($user, AppModule::Rh)) {
            $names[] = 'search_rh_employees';
            $names[] = 'search_rh_companies';
        }

        if (ModuleAccess::canViewModule($user, AppModule::Crm)) {
            $names = array_merge($names, [
                'search_crm_accounts',
                'get_crm_account',
                'search_crm_contacts',
                'search_crm_opportunities',
                'search_crm_calendar_events',
                'search_crm_account_contacts',
            ]);
        }

        if (ModuleAccess::canViewModule($user, AppModule::Comercial)) {
            $names[] = 'search_commercial_quotes';
            $names[] = 'get_commercial_quote_summary';
        }

        if (ModuleAccess::canViewModule($user, AppModule::Helpdesk)) {
            $names = array_merge($names, [
                'search_helpdesk_tickets',
                'search_helpdesk_contracts',
                'search_helpdesk_coverages',
                'search_helpdesk_service_agreements',
                'search_helpdesk_categories',
            ]);
        }

        return $names;
    }

    protected function moduleSummary(User $user): string
    {
        $lines = [];

        foreach (AppModule::cases() as $module) {
            $level = $user->permissionLevelForModule($module);
            $label = $module->getLabel();

            if ($user->isAdministrator()) {
                $lines[] = "- {$label}: administrador (acceso total).";

                continue;
            }

            if ($level === null) {
                $lines[] = "- {$label}: sin acceso.";

                continue;
            }

            $lines[] = "- {$label}: {$level->getLabel()}.";
        }

        return implode("\n", $lines);
    }

    protected function constraintSummary(User $user): string
    {
        $lines = [];

        if ($user->crm_record_scope === CrmRecordScope::Own) {
            $lines[] = '- CRM: solo oportunidades asignadas a este usuario.';
        }

        if (! $user->canCreateHelpdeskTickets()) {
            $lines[] = '- Helpdesk: visibilidad de tickets según política de usuario.';
        } elseif ($user->helpdesk_ticket_scope?->value === 'assigned_or_opened') {
            $lines[] = '- Helpdesk: visibilidad restringida a tickets abiertos o asignados.';
        }

        return $lines === [] ? '- Ninguna restricción adicional.' : implode("\n", $lines);
    }
}