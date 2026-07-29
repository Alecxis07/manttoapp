# TASKS — Registro de implementación MVP

> Registro vivo por fases. Estados: **Pendiente** | **En progreso** | **Completada**.  
> Actualizar al cerrar cada tarea. No marcar fases futuras como completadas anticipadamente.

**Última actualización:** 2026-07-29 (Fase 10 completada — cierre MVP implementación)

---

## Fase 0 — Fundaciones y alineación SDD

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F0-01 | Reescribir `specs/07-domain-model.md` (México, Laravel, bigint, enums) | Plan §9, RN-GEN-* | — | Completada | 2026-07-29 |
| F0-02 | Reescribir `specs/08-acceptance-criteria.md` (Gherkin, estados canónicos) | RF-* / CA | — | Completada | 2026-07-29 |
| F0-03 | Reescribir `specs/09-test-strategy.md` (PHPUnit 60/30/10) | — | — | Completada | 2026-07-29 |
| F0-04 | Crear ADR-001 … ADR-005 | Arquitectura | — | Completada | 2026-07-29 |
| F0-05 | Instalar `spatie/laravel-permission` + publish + `HasRoles` | RF-SEG-002 | Feature seeder | Completada | 2026-07-29 |
| F0-06 | Instalar `barryvdh/laravel-dompdf` | RF-COT-008, RF-HIS | — | Completada | 2026-07-29 |
| F0-07 | Habilitar TypeScript (`tsconfig`, `app.ts`, Vite) | Spec §8.3 | build | Completada | 2026-07-29 |
| F0-08 | Migraciones/modelos: settings, document_sequences, activity_logs, attachments, notes | Spec 07 §4 | Feature/Unit | Completada | 2026-07-29 |
| F0-09 | Enums de estado (orden, cotización, facturación) | RN-GEN-008 | `tests/Unit/Enums/*` | Completada | 2026-07-29 |
| F0-10 | `FolioGenerator` transaccional | RN-GEN-001/009 | `tests/Unit/Support/FolioGeneratorTest.php` | Completada | 2026-07-29 |
| F0-11 | `TotalsCalculator` (IVA settings) | RN-GEN-002 | `tests/Unit/Services/TotalsCalculatorTest.php` | Completada | 2026-07-29 |
| F0-12 | Trait `Auditable` → activity_logs | RN-GEN-004 | `tests/Unit/Models/AuditableTest.php` | Completada | 2026-07-29 |
| F0-13 | `RolesAndPermissionsSeeder` + DatabaseSeeder | Spec 03 §3 | `tests/Feature/RolesAndPermissionsSeederTest.php` | Completada | 2026-07-29 |

---

## Fase 1 — Seguridad y usuarios (MOD-001)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F1-01 | Campo `status` en users + bloqueo login inactivos | RF-SEG-001 | `tests/Feature/Authentication/InactiveUserCannotLoginTest.php` | Completada | 2026-07-29 |
| F1-02 | CRUD admin usuarios + asignación roles | RF-SEG-002 | `tests/Feature/Users/UserManagementTest.php` | Completada | 2026-07-29 |
| F1-03 | Auditoría de accesos / intentos denegados | RF-AUD-001 | `tests/Feature/Audit/AccessAuditTest.php` | Completada | 2026-07-29 |
| F1-04 | Matriz de permisos por rol | Spec 03 §3 | `tests/Feature/Users/PermissionMatrixTest.php` | Completada | 2026-07-29 |

---

## Fase 2 — Clientes (MOD-002)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F2-01 | Migraciones customers + fiscal profiles | RF-CLI-001..005 | Feature Customers | Completada | 2026-07-29 |
| F2-02 | FormRequests, Policy, Actions, Inertia pages | RN-CLI-* | Feature Customers | Completada | 2026-07-29 |

---

## Fase 3 — Unidades (MOD-003)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F3-01 | vehicle_types + vehicles + normalización placas | RF-UNI-*, RN-UNI-* | `tests/Feature/Vehicles/*`, `tests/Unit/Models/VehicleNormalizationTest.php` | Completada | 2026-07-29 |

---

## Fase 4 — Catálogos (MOD-004/007)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F4-01 | service_categories, service_catalog, part_catalog | RF-CAT-*, RN-CAT-* | `tests/Feature/Catalogs/*`, `tests/Unit/Models/CatalogConceptUsageTest.php` | Completada | 2026-07-29 |

---

## Fase 5 — Órdenes (MOD-005)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F5-01 | Órdenes, partidas, state machine, evidencias | RF-ORD-*, RN-ORD-* | `tests/Unit/Enums/MaintenanceOrderStatusTest.php`, `tests/Feature/MaintenanceOrders/*` | Completada | 2026-07-29 |

---

## Fase 6 — Historial (MOD-006)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F6-01 | Timeline expediente + PDF | RF-HIS-001, RF-HIS-002, RF-HIS-007 | `tests/Unit/Services/VehicleExpedienteTimelineTest.php`, `tests/Feature/Vehicles/VehicleExpedienteTest.php` | Completada | 2026-07-29 |

---

## Fase 7 — Cotizaciones (MOD-008)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F7-01 | Versionado, descuentos, conversión, PDF | RF-COT-*, RN-COT-* | `tests/Feature/Quotations/*` | Completada | 2026-07-29 |

---

## Fase 8 — Facturación (MOD-009)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F8-01 | 7 estados, snapshot fiscal, invoice_reference | RF-FAC-*, RN-FAC-* | `tests/Feature/BillingRequests/*`, `tests/Unit/Enums/StatusEnumsTest.php` | Completada | 2026-07-29 |

---

## Fase 9 — Dashboard y reportes (MOD-010/011)

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F9-01 | KPIs, búsqueda global, reportes | RF-REP-001..005 | `tests/Feature/Reports/*` | Completada | 2026-07-29 |

---

## Fase 10 — Config, auditoría UI y cierre

| ID | Tarea | RF / RN | Tests | Estado | Fecha |
|---|---|---|---|---|---|
| F10-01 | Settings UI (IVA, adjuntos, prefijos), audit viewer, rate limit recovery, preventLazyLoading (local), suite + matriz | RF-CON-*, RF-AUD-002, RNF-SEG-004 | `tests/Feature/Settings/SettingsManagementTest.php`, `tests/Feature/Audit/AuditViewerTest.php`, `tests/Feature/Authentication/RateLimitingTest.php` | Completada | 2026-07-29 |

---

## Checklist MVP (implementación)

| Ítem | Estado |
|---|---|
| Fases 0–10 | Completadas |
| Matriz de trazabilidad | `SDD/specs/traceability-matrix.md` |
| ADRs 001–005 | Completados |

---

## Notas

- Fases 2 y 4 pueden iniciar en paralelo tras Fase 1 (ver plan aprobado).
- Al completar una fase, marcar tareas y anotar fecha.
- Fase 5 requiere Fase 3 y Fase 4 completadas.
- Cierre operativo (UAT, staging, aprobaciones de negocio) permanece fuera del alcance de implementación de código.
