# Matriz de trazabilidad — MVP Mantenimiento

> Cadena: **Objetivo → RF → RN → Módulo → CA → Test**  
> Actualizado: 2026-07-29 (cierre Fase 10)

---

## Resumen por módulo

| Módulo | Objetivo MVP | RF principales | RN | CA | Tests clave |
|---|---|---|---|---|---|
| MOD-001 | Auth + usuarios/roles | RF-SEG-001..004 | Spec 03 matriz | CA auth / permisos | `Authentication/*`, `Users/*`, `RolesAndPermissionsSeederTest` |
| MOD-002 | Clientes + fiscal | RF-CLI-001..005 | RN-CLI-001..003 | CA-CLI-* | `Customers/*` |
| MOD-003 | Unidades | RF-UNI-001..005 | RN-UNI-001..004 | CA-UNI-* | `Vehicles/*`, `VehicleNormalizationTest` |
| MOD-004/007 | Catálogos servicios/refacciones | RF-CAT-001..004 | RN-CAT-001..002 | CA-CAT-* | `Catalogs/*`, `CatalogConceptUsageTest` |
| MOD-005 | Órdenes de mantenimiento | RF-ORD-001..008 | RN-ORD-001..004, RN-GEN-* | CA-ORD-* | `MaintenanceOrders/*`, `MaintenanceOrderStatusTest` |
| MOD-006 | Expediente / historial | RF-HIS-001..002, RF-HIS-007 | — | CA-HIS-* | `VehicleExpediente*`, `VehicleExpedienteTimelineTest` |
| MOD-008 | Cotizaciones | RF-COT-001..008 | RN-COT-001..003 | CA-COT-* | `Quotations/*` |
| MOD-009 | Solicitudes de facturación | RF-FAC-001..005 | RN-FAC-001..003 | CA-FAC-* | `BillingRequests/*` |
| MOD-010/011 | Dashboard, búsqueda, reportes | RF-REP-001..005 | — | CA-REP-* | `Reports/*` |
| MOD-012 | Auditoría | RF-AUD-001..002 | RN-GEN-004 | CA-AUD-001 | `Audit/*`, `AuditableTest` |
| MOD-013 | Adjuntos | RF-ARC-001..003 | RN-GEN-006 | (órdenes) | `MaintenanceOrders` attachments |
| MOD-014 | Configuración | RF-CON-001..002 | RN-GEN-001/002/009 | CA-CON-001..002 | `Settings/SettingsManagementTest` |

---

## Cadena detallada (críticos Fase 10 + transversales)

| Objetivo | RF | RN | Módulo | CA | Test |
|---|---|---|---|---|---|
| Configurar IVA / adjuntos / operación | RF-CON-001 | RN-GEN-002, RN-GEN-003, RN-GEN-006 | MOD-014 | CA-CON-001 | `SettingsManagementTest` |
| Prefijos y correlativos de folios | RF-CON-002 | RN-GEN-001, RN-GEN-009 | MOD-014 | CA-CON-002 | `SettingsManagementTest`, `FolioGeneratorTest` |
| Registrar actividad crítica | RF-AUD-001 | RN-GEN-004 | MOD-012 | CA-AUD-001 | `AccessAuditTest`, `AuditableTest` |
| Consultar auditoría filtrable | RF-AUD-002 | Matriz permisos | MOD-012 | CA-AUD-001 (consulta) | `AuditViewerTest` |
| Rate limiting login / recovery | RNF-SEG-004 | — | MOD-001 | — | `RateLimitingTest` |
| Folios únicos concurrentes | RF-ORD/COT/FAC | RN-GEN-001/009 | transversal | CA-GEN-FOLIO | `FolioGeneratorTest` |
| Totales e IVA en servidor | — | RN-GEN-002 | transversal | CA-GEN-MONEY | `TotalsCalculatorTest`, `MaintenanceOrderTotalsTest` |
| Snapshot precios/IVA/fiscal | — | RN-GEN-003 | ORD/COT/FAC | CA-GEN-SNAP | Quotations/Billing feature tests |

---

## Cobertura de permisos (settings / audit)

| Permiso | admin | administrativo | técnico | consulta | Tests |
|---|---|---|---|---|---|
| `settings.*` | Sí | No | No | No | `SettingsManagementTest`, `PermissionMatrixTest` |
| `audit.view` | Sí | Sí | No | No | `AuditViewerTest`, `PermissionMatrixTest` |

---

## DoD MVP (implementación)

| Criterio | Estado |
|---|---|
| Módulos MOD-001 … MOD-014 implementados | Completado (código + tests por fase) |
| Suite automatizada PHPUnit | 207+ tests verdes (ver corrida Fase 10) |
| Specs 07/08/09 + ADRs + TASKS | Completado |
| Matriz de trazabilidad | Este documento |
| Validación UAT / staging / sin incidencias críticas | Pendiente operativo (fuera de implementación) |
