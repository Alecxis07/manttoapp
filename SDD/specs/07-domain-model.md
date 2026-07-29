# 07 — Modelo de Dominio

> **Archivo:** `specs/07-domain-model.md`  
> **Versión:** 2.0.0  
> **Estado:** Alineado a plan de implementación MVP  
> **Stack:** Laravel 12, Eloquent, MySQL, PHP 8.2  
> **Contexto fiscal:** México (SAT / RFC / IVA / MXN). Sin timbrado CFDI en MVP.

---

## 1. Propósito

Define el modelo de dominio Eloquent/MySQL del Sistema de Mantenimiento Preventivo para Unidades: entidades, atributos, relaciones, enums canónicos e índices mínimos, alineado a `PLAN_DESARROLLO_SDD_MANTENIMIENTO_UNIDADES.md` §9 y a los specs `00`–`06`.

## 2. Convenciones

| Convención | Valor |
|---|---|
| IDs | `bigint` autoincremental |
| Tablas / columnas | inglés, `snake_case` |
| Dinero | `decimal(12,2)` |
| Zona horaria de negocio | `America/Mexico_City` |
| Persistencia de fechas | UTC |
| Moneda | MXN |
| Impuesto | IVA configurable (default 16%) en `settings`; snapshot por documento |
| Soft delete / baja | `status` o `deleted_at` según módulo (RN-GEN-005) |
| Roles | `spatie/laravel-permission` (roles globales; Jetstream Teams no se usa para tenancy) |

---

## 3. Enums canónicos

### 3.1 `MaintenanceOrderStatus`

`received` → `diagnosing` → `pending_approval` → `approved` → `in_progress` → `completed` → `delivered`

- `cancelled` desde cualquier estado (solo admin).
- Reapertura: `delivered` → `in_progress` (solo admin, RN-ORD-002 / RF-ORD-008).

### 3.2 `QuotationStatus`

`draft` → `sent` → `accepted` | `rejected`

- `expired` por vigencia (`valid_until`).
- `cancelled`.

### 3.3 `BillingRequestStatus`

`draft` → `pending_review` → `incomplete` | `approved` → `processed`

- Terminales / laterales: `rejected`, `cancelled`.
- `processed` registra `invoice_reference` (referencia manual; sin CFDI en MVP).

---

## 4. Infraestructura base (Fase 0)

### 4.1 `settings`

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| key | string unique | ej. `tax.iva_rate` |
| value | text | valor serializado |
| type | string | `string`, `integer`, `decimal`, `boolean`, `json` |
| group | string nullable | ej. `tax`, `files`, `folios` |
| description | string nullable | |
| timestamps | | |

Semilla mínima: `tax.iva_rate` = `16.00`.

### 4.2 `document_sequences`

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| document_type | string unique | `maintenance_order`, `quotation`, `billing_request` |
| prefix | string | `ORD`, `COT`, `FAC` |
| year | unsigned smallint | año del correlativo |
| last_number | unsigned integer | último usado |
| padding | unsigned tinyint | default 5 |
| timestamps | | |

Usado por `App\Support\FolioGenerator` con `lockForUpdate()` (RN-GEN-001 / RN-GEN-009). Formato: `{PREFIX}-{YYYY}-{#####}`.

### 4.3 `activity_logs`

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | FK nullable | usuario actor |
| action | string | `created`, `updated`, `deleted`, `login`, etc. |
| subject_type | string nullable | morph |
| subject_id | bigint unsigned nullable | morph |
| properties | json nullable | `before` / `after` |
| ip_address | string nullable | |
| user_agent | string nullable | |
| created_at | timestamp | inmutable (sin `updated_at`) |

### 4.4 `attachments` (polimórfica)

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| attachable_type / attachable_id | morph | |
| disk | string | default `local` / `public` |
| path | string | |
| original_name | string | |
| mime_type | string | |
| size | unsigned bigint | bytes |
| uploaded_by | FK users nullable | |
| timestamps | | |
| deleted_at | soft delete nullable | RN-GEN-005 / RF-ARC-003 |

### 4.5 `notes` (polimórfica)

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| notable_type / notable_id | morph | |
| body | text | |
| created_by / updated_by | FK users nullable | |
| timestamps | | |

---

## 5. Entidades de autenticación y autorización

### 5.1 `users` (Jetstream + Fortify + spatie)

Campos Jetstream existentes + (Fase 1) `status` (`active` / `inactive`). Relación: roles/permisos vía `HasRoles`.

### 5.2 Roles y permisos (spatie)

Tablas del paquete: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

Roles MVP: `admin`, `administrativo`, `tecnico`, `consulta` — matriz en `03-actors-permissions.md` §3.

---

## 6. Clientes y unidades

### 6.1 `customers`

`type` (`individual`|`company`), `name`, `trade_name`, `phone`, `email`, `status`, `created_by`, `updated_by`, timestamps.

### 6.2 `customer_fiscal_profiles`

`customer_id`, `legal_name`, `rfc`, `tax_regime_code`, `cfdi_use_code`, `postal_code`, `email`, `is_default` (único por cliente activo), timestamps.

Campos fiscales México/SAT. Timbrado CFDI fuera de MVP; se usan para snapshot en solicitudes.

### 6.3 `vehicle_types` / `vehicles`

Unidad: `customer_id`, `vehicle_type_id`, `license_plate`, `license_plate_normalized` (único), `vin` (único nullable), `economic_number`, `brand`, `model`, `year`, `engine_type`, `current_mileage`, `status`, timestamps.

---

## 7. Catálogos

- `service_categories`
- `service_catalog` — código, descripción, categoría, precio base, unidad, tiempo estimado, `is_active`
- `part_catalog` — análogo para refacciones/conceptos

Precios de catálogo son referenciales (RN-CAT-002); documentos copian snapshot (RN-GEN-003).

---

## 8. Órdenes de mantenimiento

### 8.1 `maintenance_orders`

`folio`, `customer_id`, `vehicle_id`, `quotation_id` nullable, `type` (preventivo/correctivo), `status`, fechas (`received_at`…`delivered_at`), `mileage`, `reason`, `diagnosis`, `technical_notes`, importes (`subtotal`, `discount_total`, `tax_total`, `total`), `tax_rate` (snapshot IVA), `assigned_user_id`, `created_by`, `updated_by`, timestamps.

### 8.2 `maintenance_order_items` / `maintenance_parts`

Partidas con descripción, cantidad, precio unitario snapshot, descuento, totales de línea.

### 8.3 `maintenance_status_history`

`maintenance_order_id`, `from_status`, `to_status`, `user_id`, `notes`, `created_at`.

---

## 9. Cotizaciones

### 9.1 `quotations`

`folio`, `version`, `parent_quotation_id` nullable, `customer_id`, `vehicle_id`, `status`, `issued_at`, `valid_until`, importes + `tax_rate` snapshot, `commercial_terms`, `accepted_at`, `accepted_by`, timestamps.

### 9.2 `quotation_items` / `quotation_status_history`

Analogía a órdenes.

---

## 10. Solicitudes de facturación

### 10.1 `billing_requests`

`folio`, `customer_id`, `vehicle_id` nullable, `maintenance_order_id` / `quotation_id` (origen), `fiscal_profile_snapshot` JSON, `status`, `payment_method_code`, `payment_form_code` (catálogo SAT), `currency` (MXN), importes + `tax_rate`, `invoice_reference` nullable, `requested_by`, `processed_by`, timestamps.

Sin emisión/timbrado CFDI en MVP.

### 10.2 `billing_request_items` / `billing_request_status_history`

---

## 11. Relaciones principales

```text
Customer 1──* Vehicle
Customer 1──* CustomerFiscalProfile
Vehicle 1──* MaintenanceOrder
Customer/Vehicle 1──* Quotation
MaintenanceOrder|Quotation ──* BillingRequest
* ── morph Attachment / Note
User ──* ActivityLog
```

- Unidad pertenece a un solo cliente (RN-ORD-001 / route `scopeBindings`).
- Adjuntos polimórficos a órdenes, unidades, cotizaciones, etc.

---

## 12. Índices mínimos

- `customers(name)`, `customers(email)`
- `vehicles(customer_id, status)`, `vehicles(license_plate_normalized)` unique, `vehicles(vin)` unique nullable
- `maintenance_orders(vehicle_id, received_at)`, `maintenance_orders(status, received_at)`, `maintenance_orders(folio)` unique
- `quotations(status, issued_at)`, `quotations(folio, version)` unique
- `billing_requests(status, created_at)`, `billing_requests(folio)` unique
- FKs indexadas; morph indexes en `attachments` / `notes` / `activity_logs`

---

## 13. Clases de soporte de dominio

| Clase | Responsabilidad | RN |
|---|---|---|
| `App\Support\FolioGenerator` | Secuencia transaccional | RN-GEN-001, RN-GEN-009 |
| `App\Services\TotalsCalculator` | Subtotal / descuento / IVA / total | RN-GEN-002 |
| `App\Models\Concerns\Auditable` | Escritura a `activity_logs` | RN-GEN-004 |
| `App\Enums\*` | Máquinas de estado | RN-GEN-008 |

---

## 14. Fuera de alcance (MVP)

- Timbrado CFDI / PAC
- Inventario / kardex completo
- Multi-tenancy
- UUID como PK

---

## 15. Control de cambios

| Versión | Fecha | Cambio |
|---|---|---|
| 1.0 | 2026-01-15 | Borrador obsoleto (UUID, contexto ajeno) |
| 2.0.0 | 2026-07-29 | Reescritura México/SAT, Laravel 12, enums canónicos, bigint |
