# DBPI — Especificaciones de la aplicación

**DBPI** — Dynacom Business Process Integration

> Documento vivo: se actualiza conforme avance el desarrollo.  
> **Última revisión:** 2025-11-25  
> **Stack:** Laravel 12 + Filament 5 (panel `/admin`)

---

## 1. Visión general

Aplicación modular para operación interna de negocio. Los módulos comparten datos y flujos donde tenga sentido (empleados, empresas, clientes, tickets, etc.) sin duplicar información ni romper el aislamiento por contexto de cada área.

### 1.1 Módulos

| Módulo | Código interno | Estado | Prioridad |
|--------|----------------|--------|-----------|
| Recursos Humanos | `rh` | **En desarrollo (fase 1)** | Alta |
| Comercial | `comercial` | Pendiente | — |
| Helpdesk | `helpdesk` | Pendiente | — |
| CRM | `crm` | Pendiente | — |

### 1.2 Principios transversales

- **Idioma:** la aplicación completa debe estar en **español** (interfaz del panel, etiquetas, mensajes de validación, notificaciones, correos y textos orientados al usuario). Código, nombres de variables y comentarios técnicos pueden permanecer en inglés según convención Laravel.
- **Multi-empresa:** varias empresas (razones sociales / unidades de negocio) en el mismo sistema.
- **Modularidad:** cada módulo con recursos Filament y modelos propios; relaciones explícitas entre módulos cuando se implementen integraciones.
- **Documentos genéricos:** almacenamiento de archivos asociados a entidades (empleado, empresa, etc.) con tipo/categoría configurable, no hardcodeado a un solo tipo de archivo.
- **Auditoría (futuro):** registrar quién y cuándo modifica datos sensibles (sueldos, documentos).

---

## 2. Módulo: Recursos Humanos (`rh`)

### 2.1 Objetivo

Gestionar empresas registradas en el sistema, sus empleados, datos personales y laborales, compensación y expediente documental digital.

### 2.2 Alcance fase 1 (actual)

- CRUD de **empresas** (catálogo maestro del módulo RH).
- CRUD de **empleados** vinculados a una empresa.
- Información **personal** del empleado.
- Información de **compensación / sueldo** (estructura base; detalle en §2.5).
- **Expediente documental** por empleado: subida, clasificación y consulta de archivos de cualquier tipo permitido (INE, estudio socioeconómico, constancia, contratos, etc.).

### 2.3 Fuera de alcance fase 1 (pendiente RH)

- Nómina, timbrado, INCAP, prestaciones calculadas.
- Organigrama, puestos y jerarquías.
- Asistencia, vacaciones y permisos.
- Portal del empleado (autoservicio).
- Firma electrónica de documentos.
- Integración con Comercial / CRM / Helpdesk (ver §5).

### 2.4 Entidades propuestas

#### Empresa (`companies` o equivalente)

| Campo / aspecto | Notas |
|---------------|--------|
| Razón social, nombre comercial | Obligatorios según negocio |
| RFC, régimen fiscal (opcional fase 1) | Útil para reportes futuros |
| Dirección, contacto | Opcional fase 1 |
| Activo / inactivo | Soft delete o flag `is_active` |

#### Empleado (`employees`)

| Campo / aspecto | Notas |
|---------------|--------|
| `company_id` | Pertenece a una empresa |
| Datos personales | Nombre(s), apellidos, CURP, RFC, NSS, fecha nacimiento, género, estado civil, contacto (email, teléfono), domicilio |
| Datos laborales | Fecha ingreso, fecha baja (nullable), número de empleado interno, estatus (activo, baja, suspendido) |
| Relación 1:N con documentos y con registros de sueldo |

#### Compensación (`employee_compensations` o campos en empleado — por definir en implementación)

| Campo / aspecto | Notas |
|---------------|--------|
| Sueldo base, periodicidad (mensual, quincenal, etc.) | Histórico recomendado (nueva fila al cambiar sueldo) |
| Moneda | Default MXN |
| Vigencia (`effective_from`, `effective_to`) | Para historial |
| Notas internas | No visibles en portal futuro sin permiso |

#### Tipo de documento (`document_types`) — catálogo genérico

| Campo / aspecto | Notas |
|---------------|--------|
| Nombre, código slug | Ej. `ine`, `estudio_socioeconomico`, `constancia` |
| Módulo / contexto | `rh` (extensible a otros módulos) |
| Requiere vencimiento | Opcional |
| Activo | Para ocultar tipos obsoletos sin borrar historial |

#### Documento (`documents` — polimórfico o `employee_documents` + diseño genérico)

| Campo / aspecto | Notas |
|---------------|--------|
| Entidad dueña | Empleado (fase 1); patrón polimórfico `documentable` para reutilizar en otros módulos |
| `document_type_id` | Clasificación |
| Archivo | Disco configurado (`local` / `s3` vía `config/filament.php`) |
| Nombre original, MIME, tamaño | Metadata |
| Fecha emisión / vencimiento | Opcional según tipo |
| Subido por (`user_id`) | Usuario del panel |
| Notas | Texto libre |

### 2.5 Reglas de negocio (borrador)

1. Un empleado pertenece a **una** empresa (fase 1). Reasignación entre empresas = caso especial (pendiente).
2. No eliminar físicamente documentos con valor legal; preferir **inactivar** o soft delete.
3. Tipos de documento administrables desde el panel (seed inicial con ejemplos: INE, Estudio socioeconómico, Constancia, CURP, comprobante domicilio, contrato).
4. Extensiones y tamaño máximo de archivo: definir en configuración (pendiente valores exactos).

### 2.6 UI Filament (fase 1)

- Grupo de navegación: **Recursos Humanos**
- Resources: Empresas, Empleados, Tipos de documento
- En empleado: pestañas o relation managers para **Compensación** y **Documentos** (upload + lista + descarga)
- Policies / roles: mínimo admin con acceso total (refinar roles después)

### 2.7 Criterios de aceptación fase 1

- [ ] Crear/editar/listar empresas
- [ ] Crear/editar/listar empleados por empresa
- [ ] Registrar y consultar historial de sueldo (al menos un registro vigente)
- [ ] Subir documento con tipo seleccionable y descargar desde el panel
- [ ] Seed de tipos de documento RH de ejemplo

---

## 3. Módulo: Comercial — *Pendiente*

> Placeholder hasta definir alcance.

**Ideas iniciales (sin compromiso):**

- Catálogo de productos/servicios, cotizaciones, órdenes.
- Vinculación futura con empresas/clientes del CRM.

**Dependencias RH:** ninguna obligatoria en fase 1.

---

## 4. Módulo: Helpdesk — *Pendiente*

> Placeholder hasta definir alcance.

**Ideas iniciales (sin compromiso):**

- Tickets, categorías, SLA, asignación a usuarios/agentes.
- Posible relación con empleados (solicitante interno) o contactos CRM.

---

## 5. Módulo: CRM — *Pendiente*

> Placeholder hasta definir alcance.

**Ideas iniciales (sin compromiso):**

- Prospectos, clientes, oportunidades, actividades.
- Posible uso de la misma entidad **Empresa** o modelo `Account` compartido (decisión pendiente para evitar duplicar razones sociales).

---

## 6. Integración entre módulos — *Pendiente*

| Integración | Descripción | Estado |
|-------------|-------------|--------|
| RH ↔ CRM | Empresa cliente vs empresa empleadora | Por definir |
| RH ↔ Helpdesk | Empleado como usuario solicitante | Por definir |
| Comercial ↔ CRM | Cotización → oportunidad | Por definir |

---

## 7. Modelo de datos (diagrama conceptual)

```mermaid
erDiagram
    COMPANY ||--o{ EMPLOYEE : tiene
    EMPLOYEE ||--o{ EMPLOYEE_COMPENSATION : historial
    EMPLOYEE ||--o{ DOCUMENT : expediente
    DOCUMENT_TYPE ||--o{ DOCUMENT : clasifica
```

---

## 8. Decisiones técnicas

| Tema | Decisión | Fecha |
|------|----------|-------|
| Idioma de la aplicación | Español (`APP_LOCALE=es`). Filament 5 trae `lang/es` en sus paquetes y usa `app()->getLocale()`; no requiere `->locale()` en el panel | 2025-11-25 |
| Panel admin | Filament 5, ruta `/admin` | 2025-11-25 |
| BD inicial | SQLite (desarrollo) | 2025-11-25 |
| Documentos | Catálogo `document_types` + almacenamiento en disco Laravel | 2025-11-25 |
| Polimorfismo documentos | Recomendado para reuso inter-módulos | 2025-11-25 (propuesta) |

### 8.1 Decisiones abiertas

- [ ] ¿Una sola tabla `companies` global o `rh_companies` separada de cuentas CRM?
- [ ] ¿Roles y permisos por módulo (Spatie Permission u otro)?
- [ ] Disco de producción para documentos (S3, local cifrado, etc.)
- [ ] Campos obligatorios legales por empleado (CURP, RFC, NSS)

---

## 9. Historial de cambios

| Fecha | Cambio |
|-------|--------|
| 2025-11-25 | Creación del documento. Módulo RH definido para fase 1; demás módulos en pendiente. |
| 2025-11-25 | Requisito de idioma: aplicación completa en español. |
| 2025-11-25 | Panel Filament en español vía `APP_LOCALE=es` (Laravel). |

---

## 10. Próximos pasos de desarrollo

1. Migraciones y modelos: Empresa, Empleado, Compensación, TipoDocumento, Documento.
2. Filament Resources y relation managers (documentos, sueldo).
3. Seeders de tipos de documento RH.
4. Revisar decisiones abiertas (§8.1) antes de nómina o integraciones.