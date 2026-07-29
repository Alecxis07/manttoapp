# 08 — Criterios de Aceptación

> **Archivo:** `specs/08-acceptance-criteria.md`  
> **Versión:** 2.0.0  
> **Estado:** Alineado a plan de implementación MVP  
> **Formato:** Gherkin (Given / When / Then)  
> **Contexto:** México (RFC / IVA / MXN). Sin timbrado CFDI. Stack Laravel 12 + Inertia + Vue + PHPUnit.

---

## 1. Propósito

Criterios de aceptación por RF de `04-functional-requirements.md`, con estados canónicos del plan aprobado y reglas de `05-business-rules.md`. Cada CA crítico debe mapearse a un test PHPUnit (Unit o Feature) en `09-test-strategy.md`.

## 2. Convenciones de estados

| Dominio | Estados |
|---|---|
| Orden | `received` → `diagnosing` → `pending_approval` → `approved` → `in_progress` → `completed` → `delivered`; `cancelled` (admin); reopen `delivered` → `in_progress` (admin) |
| Cotización | `draft` → `sent` → `accepted`/`rejected`; `expired`; `cancelled` |
| Facturación | `draft` → `pending_review` → `incomplete`/`approved` → `processed`; `rejected`/`cancelled` |

---

## 3. Seguridad (MOD-001)

### CA-SEG-001 — Autenticación (RF-SEG-001)

```gherkin
Scenario: Login exitoso
  Given un usuario activo con credenciales válidas
  When inicia sesión
  Then se crea una sesión segura (Jetstream/Fortify)
  And solo ve módulos autorizados por su rol spatie

Scenario: Usuario inactivo
  Given un usuario con status inactive
  When intenta autenticarse
  Then el acceso es denegado
```

### CA-SEG-002 — Roles y permisos (RF-SEG-002)

```gherkin
Scenario: Acceso denegado por ruta directa
  Given un usuario sin permiso customers.update
  When solicita la ruta de edición de cliente
  Then el servidor responde 403
  And no modifica datos
  And puede registrarse en activity_logs
```

### CA-SEG-003 / CA-SEG-004 — Recuperación y cierre (RF-SEG-003, RF-SEG-004)

Cubiertos por Fortify/Jetstream; rate limiting en login/recuperación (RNF-SEG-004, Fase 10).

---

## 4. Clientes (MOD-002)

### CA-CLI-001 — Alta (RF-CLI-001)

```gherkin
Given un usuario con customers.create
When registra un cliente persona física o moral con datos válidos
Then el cliente queda activo y auditable
```

### CA-CLI-004 — Perfil fiscal México (RF-CLI-004)

```gherkin
Given un cliente
When se guarda un perfil fiscal con RFC, régimen, uso CFDI y CP
Then is_default es único por cliente
And los documentos posteriores pueden tomar snapshot (RN-GEN-003)
```

### CA-CLI-005 — Desactivación (RF-CLI-005, RN-CLI-002)

```gherkin
Given un cliente con historial
When un autorizado lo desactiva
Then no se elimina físicamente
And no aparece en selectores de nuevas operaciones (RN-CLI-002)
```

---

## 5. Unidades (MOD-003)

### CA-UNI-001 — Registro y normalización (RF-UNI-001, RN-UNI-001)

```gherkin
Given placas "abc-12-34"
When se guarda la unidad
Then license_plate_normalized es único e insensible a formato
And VIN único si se proporciona
```

### CA-UNI-004 — Búsqueda rápida (RF-UNI-004)

```gherkin
When se busca por placas con formato distinto al almacenado
Then se encuentra la unidad
```

---

## 6. Catálogos (MOD-004/007)

### CA-CAT-001 — Protección en uso (RF-CAT-*, RN-CAT-001)

```gherkin
Given un servicio del catálogo usado en una orden
When se intenta eliminar
Then se rechaza
And solo se permite desactivar
```

---

## 7. Órdenes (MOD-005)

### CA-ORD-001 — Folio (RF-ORD-001, RN-GEN-001)

```gherkin
When se crea una orden
Then el folio tiene formato ORD-YYYY-#####
And es único y generado en servidor con secuencia bloqueada
```

### CA-ORD-005 — Totales en servidor (RF-ORD-005, RN-GEN-002)

```gherkin
Given partidas con precios y descuentos
When el cliente envía totales manipulados
Then el servidor recalcula con TotalsCalculator e IVA de settings (default 16%)
And persiste tax_rate snapshot (RN-GEN-003)
```

### CA-ORD-006 — Transiciones (RF-ORD-006, RN-GEN-008)

```gherkin
Scenario Outline: Transición por rol
  Given una orden en <from>
  And un usuario con rol <role>
  When solicita transición a <to>
  Then el resultado es <outcome>

Examples: según matriz 03-actors-permissions §4.2
  (cancelled solo admin; reopen delivered→in_progress solo admin)
```

### CA-ORD-007 — Evidencias (RF-ORD-007, RN-GEN-006)

```gherkin
When se sube un archivo
Then se valida MIME real, extensión y tamaño configurable
```

### CA-ORD-008 — Reapertura (RF-ORD-008)

```gherkin
Given una orden delivered
When un admin la reabre
Then el estado pasa a in_progress
And queda auditado
```

---

## 8. Historial (MOD-006)

### CA-HIS-001 / CA-HIS-002 (RF-HIS-001, RF-HIS-002)

```gherkin
Given una unidad con órdenes, cotizaciones y evidencias
When se consulta el expediente
Then el timeline está en orden cronológico descendente filtrable
And puede exportarse a PDF (dompdf) con importes coherentes
```

---

## 9. Cotizaciones (MOD-008)

### CA-COT-001 — Creación y PDF (RF-COT-001, RF-COT-008*)

```gherkin
When se crea una cotización
Then folio COT-YYYY-#####
And PDF refleja exactamente importes almacenados
```

### CA-COT-inm — Inmutabilidad y versiones (RN-COT-001/002/003)

```gherkin
Given una cotización sent
When se intenta editar
Then se rechaza y debe versionarse
And solo la versión más reciente puede aceptarse
And una expired no es aceptable
```

### CA-COT-desc — Descuentos (spec 03 §4.3)

```gherkin
Given un técnico
When aplica descuento > 0
Then Gate approve-discount lo deniega sin aprobación
```

### CA-COT-conv — Conversión a orden (RF-COT-005)

```gherkin
When se convierte una cotización accepted
Then la orden hereda partidas y precios snapshot en una transacción
```

---

## 10. Solicitudes de facturación (MOD-009)

### CA-FAC-001 — Alta desde origen (RF-FAC-001, RN-FAC-001)

```gherkin
When se crea desde orden o cotización
Then se copia fiscal_profile_snapshot e importes
And no se timbra CFDI
```

### CA-FAC-estados (RF-FAC-004)

```gherkin
Scenario: Datos incompletos
  Given una solicitud draft sin RFC/régimen completo
  When se envía a pending_review
  Then se bloquea o pasa a incomplete (RN-FAC-002)

Scenario: Procesada
  Given approved
  When se marca processed con invoice_reference
  Then queda inmutable (RN-FAC-003)
```

---

## 11. Dashboard y reportes (MOD-010/011)

### CA-REP-001 — KPIs (RF-REP-001)

```gherkin
Given datos de fábrica conocidos
When se carga el dashboard
Then los contadores coinciden y respetan permisos del rol
```

---

## 12. Auditoría, archivos y configuración (MOD-012/014)

### CA-AUD-001 (RF-AUD-001, RN-GEN-004) — Verificado

```gherkin
When ocurre un cambio crítico
Then activity_logs registra user, action, subject, before/after
And el log no se edita ni elimina por la UI
```

**Tests:** `tests/Feature/Audit/AccessAuditTest.php`, `tests/Unit/Models/AuditableTest.php`, `tests/Feature/Audit/AuditViewerTest.php`

### CA-CON-001 (RF-CON-001) — Verificado

```gherkin
Given un admin
When actualiza tax.iva_rate
Then nuevos documentos usan la nueva tasa
And documentos previos conservan su snapshot
```

**Tests:** `tests/Feature/Settings/SettingsManagementTest.php`, `tests/Unit/Services/TotalsCalculatorTest.php`

### CA-CON-002 (RF-CON-002) — Verificado

```gherkin
When se consulta document_sequences
Then prefijos y correlativos son configurables sin romper unicidad
```

**Tests:** `tests/Feature/Settings/SettingsManagementTest.php`, `tests/Unit/Support/FolioGeneratorTest.php`
---

## 13. Criterios transversales

| ID | Descripción | RN |
|---|---|---|
| CA-GEN-FOLIO | Folios únicos concurrentes | RN-GEN-001/009 |
| CA-GEN-MONEY | `decimal(12,2)`, servidor autoridad | RN-GEN-002 |
| CA-GEN-SNAP | Snapshot precios/IVA/fiscal | RN-GEN-003 |
| CA-GEN-MX | RFC, IVA MXN; sin CFDI stamp | — |

---

## 14. Control de cambios

| Versión | Fecha | Cambio |
|---|---|---|
| 1.0 | 2026-01-15 | Borrador obsoleto (JWT, SUNAT/Perú, React) |
| 2.0.0 | 2026-07-29 | Reescritura México, estados canónicos, Laravel/PHPUnit |
