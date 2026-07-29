# ADR-001 — Monolito modular Laravel + Inertia

- **Estado:** Aceptado
- **Fecha:** 2026-07-29
- **Contexto:** MVP de mantenimiento preventivo para unidades; equipo pequeño; UI administrativa en español; sin multi-tenancy ni API pública como producto principal.

## Decisión

Implementar un **monolito modular** con Laravel 12, Jetstream (Inertia/Vue), Inertia v2 y Vue 3 + Tailwind. La lógica de negocio vive en Actions/Services/Policies dentro del mismo deploy. No se introduce un frontend SPA desacoplado ni microservicios en el MVP.

## Consecuencias

- Un solo repositorio, un deploy, sesiones web tradicionales + Sanctum para tokens API Jetstream.
- Páginas en `resources/js/Pages`; rutas nombradas con Ziggy.
- Escala horizontal vía PHP/MySQL estándar; extracción de servicios solo si el producto lo exige post-MVP.
