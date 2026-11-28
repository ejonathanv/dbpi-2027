<?php

namespace App\Support\Ai;

use App\Models\User;

class AiToolRegistry
{
    public function __construct(
        protected AiUserContextBuilder $contextBuilder,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toolsForUser(User $user): array
    {
        $enabled = $this->contextBuilder->enabledToolNames($user);
        $definitions = $this->definitions();

        return array_values(array_filter(
            $definitions,
            fn (array $tool): bool => in_array($tool['function']['name'], $enabled, true),
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function definitions(): array
    {
        return [
            $this->tool('search_rh_employees', 'Consulta empleados de RH por nombre, correo o número de empleado. Puede filtrar por empresa. No devuelve datos bancarios, CURP, NSS ni salario.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre, correo o número de empleado.'],
                'company_id' => ['type' => 'integer', 'description' => 'Opcional. ID de empresa interna.'],
            ]),
            $this->tool('search_rh_companies', 'Consulta empresas internas de RH por razón social o nombre comercial.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Razón social o nombre comercial.'],
            ]),
            $this->tool('search_crm_accounts', 'Consulta cuentas CRM por nombre o razón social.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre comercial o razón social.'],
            ]),
            $this->tool('get_crm_account', 'Obtiene el detalle consultable de una cuenta CRM y sus contactos principales.', [
                'account_id' => ['type' => 'integer', 'description' => 'ID de la cuenta CRM.'],
            ], ['account_id']),
            $this->tool('search_crm_account_contacts', 'Consulta contactos de una cuenta CRM específica por nombre o correo.', [
                'crm_account_id' => ['type' => 'integer', 'description' => 'ID de la cuenta CRM.'],
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre o correo del contacto.'],
            ], ['crm_account_id']),
            $this->tool('search_crm_contacts', 'Consulta contactos CRM globalmente por nombre o correo.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre o correo del contacto.'],
            ]),
            $this->tool('search_crm_opportunities', 'Consulta oportunidades CRM por texto y, opcionalmente, por etapa.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre o descripción de la oportunidad.'],
                'stage' => ['type' => 'string', 'description' => 'Opcional. Valor de etapa CRM, por ejemplo prospeccion o cotizacion_en_proceso.'],
            ]),
            $this->tool('search_crm_calendar_events', 'Consulta eventos de calendario CRM próximos por texto y rango de días.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Título o descripción del evento.'],
                'days_ahead' => ['type' => 'integer', 'description' => 'Opcional. Días hacia adelante desde hoy; por defecto 30.'],
            ]),
            $this->tool('search_commercial_quotes', 'Consulta cotizaciones comerciales recientes o filtradas por número, descripción, etapa de flujo o usuario creador.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Número de cotización o descripción; vacío o * lista recientes.'],
                'workflow_step' => ['type' => 'string', 'description' => 'Opcional. Valor del paso de flujo, por ejemplo in_progress o internally_authorized.'],
                'created_by_user_id' => ['type' => 'integer', 'description' => 'Opcional. ID del usuario creador.'],
            ]),
            $this->tool('get_commercial_quote_summary', 'Obtiene resumen financiero consultable de una cotización: responsable, importe cliente y margen.', [
                'commercial_quote_id' => ['type' => 'integer', 'description' => 'ID de la cotización comercial.'],
            ], ['commercial_quote_id']),
            $this->tool('search_helpdesk_tickets', 'Consulta tickets helpdesk visibles por asunto, descripción, estado o usuario asignado.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Asunto o descripción del ticket.'],
                'status' => ['type' => 'string', 'description' => 'Opcional. Valor de estado helpdesk, por ejemplo abierto, en_proceso o cerrado.'],
                'assigned_user_id' => ['type' => 'integer', 'description' => 'Opcional. ID de usuario; se usa su empleado helpdesk asignado.'],
            ]),
            $this->tool('search_helpdesk_contracts', 'Consulta contratos helpdesk por nombre.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre del contrato.'],
            ]),
            $this->tool('search_helpdesk_coverages', 'Consulta coberturas helpdesk por nombre.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre de la cobertura.'],
            ]),
            $this->tool('search_helpdesk_service_agreements', 'Consulta acuerdos de servicio helpdesk por nombre.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre del acuerdo de servicio.'],
            ]),
            $this->tool('search_helpdesk_categories', 'Consulta categorías activas de helpdesk y devuelve su ruta completa.', [
                'query' => ['type' => 'string', 'description' => 'Opcional. Nombre de la categoría.'],
            ]),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $properties
     * @param  array<int, string>  $required
     * @return array<string, mixed>
     */
    protected function tool(string $name, string $description, array $properties, array $required = []): array
    {
        $parameters = [
            'type' => 'object',
            'properties' => $properties,
            'additionalProperties' => false,
        ];

        if ($required !== []) {
            $parameters['required'] = $required;
        }

        return [
            'type' => 'function',
            'function' => [
                'name' => $name,
                'description' => $description,
                'parameters' => $parameters,
            ],
        ];
    }
}
