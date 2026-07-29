# ADR-003 — Dinero decimal y snapshots financieros

- **Estado:** Aceptado
- **Fecha:** 2026-07-29
- **Contexto:** MXN e IVA 16% (configurable). El navegador no es fuente de verdad de totales (RN-GEN-002). Documentos históricos no deben mutar si cambia el catálogo o la tasa (RN-GEN-003).

## Decisión

- Columnas monetarias: `decimal(12,2)`.
- Recálculo central en `App\Services\TotalsCalculator` leyendo IVA desde `settings` (default 16%).
- Cada documento/partida persiste precios, descuentos, `tax_rate` y (en facturación) `fiscal_profile_snapshot` JSON.
- Sin librería Money externa en MVP salvo necesidad futura.

## Consecuencias

- Tests unitarios del calculador son obligatorios en Fase 0.
- Cambios de `tax.iva_rate` solo afectan documentos nuevos.
- Comparaciones en tests usan precisión de 2 decimales (evitar float crudo).
