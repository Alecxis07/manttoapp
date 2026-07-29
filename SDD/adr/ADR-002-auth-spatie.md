# ADR-002 — Autenticación Jetstream/Fortify y autorización spatie

- **Estado:** Aceptado
- **Fecha:** 2026-07-29
- **Contexto:** Cuatro roles globales (admin, administrativo, técnico, consulta). Jetstream Teams existe en el skeleton pero **no** se usa para tenancy.

## Decisión

- **Auth:** Laravel Fortify + Jetstream (login, 2FA, reset, perfil).
- **Authorization:** `spatie/laravel-permission` con roles/permisos globales; Policies por modelo; Gates transversales (ej. `approve-discount`).
- Matriz canónica: `SDD/specs/03-actors-permissions.md` §3.

## Consecuencias

- `User` usa `HasRoles`.
- Seeders sincronizan permisos; tests Feature validan 403.
- Teams de Jetstream se conservan sin acoplar dominio de mantenimiento a `team_id`.
