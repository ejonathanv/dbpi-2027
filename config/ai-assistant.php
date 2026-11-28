<?php

use App\Enums\AppModule;

return [

    'enabled' => (bool) env('AI_ASSISTANT_ENABLED', true),

    /** Respuesta inmediata en la misma petición (útil si no hay cola activa). */
    'process_sync' => (bool) env('AI_ASSISTANT_PROCESS_SYNC', false),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 90),
    ],

    'max_history_messages' => (int) env('AI_ASSISTANT_MAX_HISTORY', 24),

    'max_tool_iterations' => 6,

    'rate_limit' => [
        'max_attempts' => (int) env('AI_ASSISTANT_RATE_LIMIT', 30),
        'decay_seconds' => (int) env('AI_ASSISTANT_RATE_DECAY', 3600),
    ],

    'search_limit' => 10,

    'agent_categories' => [
        [
            'id' => 'guide',
            'module' => null,
            'label' => 'Guía rápida',
            'icon' => 'heroicon-o-sparkles',
            'actions' => [
                [
                    'label' => '¿Qué puedes consultar?',
                    'prompt' => 'Resume en pocas viñetas qué consultas de solo lectura puedes hacer por mí en DBPI. No propongas crear registros.',
                ],
            ],
        ],
        [
            'id' => 'rh',
            'module' => AppModule::Rh->value,
            'label' => 'RH',
            'icon' => 'heroicon-o-users',
            'actions' => [
                [
                    'label' => 'Buscar empleado',
                    'prompt' => 'Ayúdame a consultar empleados de RH. Pregúntame nombre, correo, número o empresa si hace falta.',
                ],
                [
                    'label' => 'Buscar empresa',
                    'prompt' => 'Consulta empresas internas de RH por razón social o nombre comercial; pídeme el criterio si falta.',
                ],
            ],
        ],
        [
            'id' => 'crm',
            'module' => AppModule::Crm->value,
            'label' => 'CRM',
            'icon' => 'heroicon-o-building-office-2',
            'actions' => [
                [
                    'label' => 'Buscar cuenta',
                    'prompt' => 'Ayúdame a consultar una cuenta CRM. Pregúntame el nombre o criterio si no lo di.',
                ],
                [
                    'label' => 'Buscar contactos',
                    'prompt' => 'Consulta contactos CRM por nombre o correo; pídeme el criterio si falta.',
                ],
                [
                    'label' => 'Buscar oportunidades',
                    'prompt' => 'Consulta oportunidades CRM por nombre, descripción o etapa; pídeme el criterio si hace falta.',
                ],
                [
                    'label' => 'Ver calendario CRM',
                    'prompt' => 'Consulta eventos próximos del calendario CRM; usa 30 días si no indico otro rango.',
                ],
            ],
        ],
        [
            'id' => 'comercial',
            'module' => AppModule::Comercial->value,
            'label' => 'Comercial',
            'icon' => 'heroicon-o-document-text',
            'actions' => [
                [
                    'label' => 'Cotizaciones recientes',
                    'prompt' => 'Muestra cotizaciones comerciales recientes con estado, responsable y enlace de edición.',
                ],
                [
                    'label' => 'Buscar cotización',
                    'prompt' => 'Consulta cotizaciones por número, descripción, etapa de flujo o usuario creador; pídeme el dato si falta.',
                ],
                [
                    'label' => 'Resumen de cotización',
                    'prompt' => 'Obtén el resumen financiero de una cotización comercial. Pídeme el ID de cotización si no lo indiqué.',
                ],
            ],
        ],
        [
            'id' => 'helpdesk',
            'module' => AppModule::Helpdesk->value,
            'label' => 'Helpdesk',
            'icon' => 'heroicon-o-lifebuoy',
            'actions' => [
                [
                    'label' => 'Buscar ticket',
                    'prompt' => 'Consulta tickets de helpdesk por asunto, estado o responsable; pídeme el criterio si hace falta.',
                ],
                [
                    'label' => 'Buscar catálogos',
                    'prompt' => 'Consulta categorías, contratos, coberturas o acuerdos de servicio de helpdesk; pregúntame cuál catálogo revisar.',
                ],
            ],
        ],
    ],

    'modules_catalog' => [
        AppModule::Rh->value => [
            'label' => 'Recursos Humanos',
            'description' => 'Empleados y empresas internas.',
            'capabilities' => [
                'view' => 'Consultas de solo lectura de empleados y empresas internas autorizadas.',
            ],
        ],
        AppModule::Crm->value => [
            'label' => 'CRM',
            'description' => 'Cuentas, contactos, oportunidades y calendario comercial.',
            'capabilities' => [
                'view' => 'Consultas de solo lectura de cuentas, contactos, oportunidades y eventos CRM.',
            ],
        ],
        AppModule::Comercial->value => [
            'label' => 'Comercial',
            'description' => 'Cotizaciones, FUP, catálogos y flujos de autorización.',
            'capabilities' => [
                'view' => 'Consultas de solo lectura de cotizaciones, estados y resumen financiero.',
            ],
        ],
        AppModule::Helpdesk->value => [
            'label' => 'Helpdesk',
            'description' => 'Tickets de soporte interno, contratos, coberturas y acuerdos de servicio.',
            'capabilities' => [
                'view' => 'Consultas de solo lectura de tickets visibles y catálogos helpdesk.',
            ],
        ],
    ],
];
