# ADR-004 — Almacenamiento de archivos

- **Estado:** Aceptado
- **Fecha:** 2026-07-29
- **Contexto:** Evidencias de órdenes, fotos de unidades y adjuntos diversos (RF-ARC-*, RN-GEN-006).

## Decisión

- Tabla polimórfica `attachments` con disk, path, MIME, tamaño y `uploaded_by`.
- Disco Laravel (`local`/`public` en desarrollo; S3 u objeto compatible en producción vía `FILESYSTEM_DISK`).
- Validación de extensión, tamaño configurable en `settings` y MIME real (contenido), no solo nombre de archivo.
- Eliminación lógica preferida cuando el adjunto forme parte del expediente (RN-GEN-005).

## Consecuencias

- Un solo modelo/servicio de adjuntos reutilizable.
- Jobs de borrado físico diferido opcionales post-MVP.
- Límites de upload alineados a RNF y configuración admin (Fase 10 UI).
