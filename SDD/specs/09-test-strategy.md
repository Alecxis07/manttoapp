# 09 — Estrategia de Pruebas

> **Archivo:** `specs/09-test-strategy.md`  
> **Versión:** 2.0.0  
> **Estado:** Alineado a plan de implementación MVP  
> **Framework:** PHPUnit 11 (`php artisan test`)  
> **Sin:** Pest, Jest, Cypress, k6 como stack obligatorio del MVP

---

## 1. Propósito

Definir cómo se prueba el MVP: pirámide, tipos de test, ubicación de archivos, datos de prueba (factories) y trazabilidad RF/RN → CA → Test.

## 2. Pirámide (60 / 30 / 10)

```text
                 ┌──────────────┐
                 │ Feature E2E  │  ~10%  Flujos HTTP Inertia críticos
                ╱│  (PHPUnit)   │╲
               ╱ └──────────────┘ ╲
              ╱   Feature módulo   ╲  ~30%  Policies, Actions, Rutas
             ╱    (PHPUnit)         ╲
            ╱────────────────────────╲
           ╱     Unit                 ╲  ~60%  Enums, FolioGenerator,
          ╱      (PHPUnit)             ╲       TotalsCalculator, rules
         ╱──────────────────────────────╲
```

- **Unit:** clases puras / dominio sin browser.
- **Feature:** HTTP + DB (`RefreshDatabase`), actingAs + roles spatie.
- **UI browser:** opcional/manual en MVP; no Cypress obligatorio.

## 3. Herramientas

| Área | Herramienta |
|---|---|
| Unit / Feature | PHPUnit 11 via `php artisan test --compact` |
| DB en tests | SQLite `:memory:` (phpunit.xml) |
| Factories / Faker | Model factories Laravel |
| Estilo PHP | Pint (`vendor/bin/pint --dirty --format agent`) |
| Frontend | Compilación Vite; tests JS no son puerta de merge del MVP |
| PDF | Assertions de generación/contenido en Feature (dompdf) |

## 4. Organización de tests

```text
tests/
├── Unit/
│   ├── Enums/
│   ├── Services/          # TotalsCalculatorTest
│   └── Support/           # FolioGeneratorTest
├── Feature/
│   ├── Customers/
│   ├── Vehicles/
│   ├── Catalogs/
│   ├── MaintenanceOrders/
│   ├── Quotations/
│   ├── BillingRequests/
│   ├── Reports/
│   ├── Settings/
│   └── Audit/
└── TestCase.php
```

Convención de nombres: `{Subject}Test.php`, métodos `test_*` o atributos `#[Test]`.

## 5. Enfoque TDD por módulo

1. Escribir Feature/Unit en rojo según CA del spec 08 y RN.
2. Migración, modelo, factory, FormRequest, Policy, Action, Controller, página Inertia.
3. Verde → refactor → Pint → actualizar `SDD/TASKS.md`.
4. Por tarea: `php artisan test --compact --filter=...`
5. Por fase: suite de la fase. Cierre: suite completa.

## 6. Cobertura mínima por fase (Fase 0)

| Componente | Test | RN / CA |
|---|---|---|
| `MaintenanceOrderStatus` / `QuotationStatus` / `BillingRequestStatus` | Unit Enums | RN-GEN-008 |
| `FolioGenerator` | Unit + concurrencia básica | RN-GEN-001/009 |
| `TotalsCalculator` | Unit IVA 16% / settings | RN-GEN-002 |
| `Auditable` | Unit/Feature escribe `activity_logs` | RN-GEN-004 |
| `RolesAndPermissionsSeeder` | Feature cuenta roles/permisos | Spec 03 §3 |

## 7. Datos y aislamiento

- Usar factories; no depender de seeders de demo en assertions (salvo test del seeder).
- `RefreshDatabase` en Feature y Unit que toquen DB.
- Asignar roles con `assignRole` / `givePermissionTo` tras migrar tablas spatie.
- Dinero: comparar strings/`decimal` con 2 decimales; no floats sueltos.

## 8. Matriz de autorización

Cada módulo Feature debe incluir:

- Happy path con rol permitido.
- 403 con rol insuficiente.
- Transiciones de estado ilegales (órdenes / cotizaciones / facturación).

Referencia: `03-actors-permissions.md` §3 y §4.

## 9. Criterios de calidad

| Criterio | Meta MVP |
|---|---|
| CA críticos automatizados | 100% de los marcados Crítica en spec 08 |
| Tests flaky | 0 en suite CI local |
| N+1 en listados | Prevenir con `with()`; `preventLazyLoading` en local (Fase 10) |
| Secretos | Nunca en fixtures |

## 10. Qué no entra en la estrategia MVP

- Jest / Testing Library como gate.
- Cypress / Playwright como suite obligatoria.
- k6 / carga formal (solo notas RNF).
- Timbrado CFDI / PAC (fuera de alcance).

## 11. Comandos

```bash
php artisan test --compact --filter=FolioGenerator
php artisan test --compact tests/Unit/Services/TotalsCalculatorTest.php
vendor/bin/pint --dirty --format agent
```

## 12. Control de cambios

| Versión | Fecha | Cambio |
|---|---|---|
| 1.0 | 2026-01-15 | Borrador obsoleto (Jest/Cypress/k6) |
| 2.0.0 | 2026-07-29 | PHPUnit Unit/Feature, pirámide 60/30/10, trazabilidad SDD |
