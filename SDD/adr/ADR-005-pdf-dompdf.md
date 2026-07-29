# ADR-005 — Generación de PDF con Dompdf

- **Estado:** Aceptado
- **Fecha:** 2026-07-29
- **Contexto:** PDF de cotizaciones (RF-COT-008) e historial/expediente (RF-HIS-007 / RF-HIS-002). Los importes del PDF deben coincidir con los almacenados.

## Decisión

Usar **`barryvdh/laravel-dompdf`** (Dompdf) con vistas Blade dedicadas. Generación síncrona en MVP; jobs en cola si el render es pesado (RNF-REN-005).

## Consecuencias

- Dependencia Composer añadida en Fase 0.
- CSS limitado a lo que Dompdf soporta; diseño de PDF funcional, no pixel-perfect browser.
- Tests Feature verifican generación y totales, no captura visual exhaustiva.
- Alternativas (Browsershot/Snappy) quedan fuera del MVP.
