# Alcance del Proyecto — Sistema de Mantenimiento Preventivo para Unidades

> **Archivo:** `01-scope.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Metodología:** Specification-Driven Development (SDD)  
> **Documento maestro:** `PLAN_DESARROLLO_SDD_MANTENIMIENTO_UNIDADES.md`

---

## 1. Módulos Incluidos en el MVP

| ID | Módulo | Descripción |
|---|---|---|
| MOD-001 | Autenticación y administración de usuarios | Gestión de usuarios, roles y permisos mediante Laravel Jetstream. |
| MOD-002 | Clientes | Registro y administración de clientes (personas físicas y morales). |
| MOD-003 | Unidades | Registro y administración de unidades de carga pesada asociadas a clientes. |
| MOD-004 | Catálogo de servicios | Definición de servicios estandarizados y conceptos facturables. |
| MOD-005 | Órdenes de mantenimiento | Registro de mantenimientos preventivos y correctivos con diagnóstico, servicios y refacciones. |
| MOD-006 | Historial | Expediente digital completo por unidad con todos los servicios realizados. |
| MOD-007 | Refacciones | Catálogo de refacciones y registro de materiales utilizados en servicios. |
| MOD-008 | Cotizaciones | Elaboración de propuestas económicas para servicios y refacciones. |
| MOD-009 | Solicitudes de facturación | Concentración de información fiscal para emisión de comprobantes. |
| MOD-010 | Reportes | Consultas, filtros y reportes administrativos. |
| MOD-011 | Dashboard | Panel principal con indicadores clave de gestión. |
| MOD-012 | Auditoría | Trazabilidad básica de operaciones críticas. |
| MOD-013 | Archivos adjuntos | Gestión de evidencias fotográficas y documentos relacionados. |
| MOD-014 | Configuración | Parámetros generales del sistema. |

---

## 2. Funciones Posteriores al MVP (Roadmap)

| ID | Función | Prioridad | Comentario |
|---|---|---|---|
| FUT-001 | Inventario completo de refacciones | Media | Existencias, almacenes, entradas, salidas y kardex. |
| FUT-002 | Alertas de próximos mantenimientos | Alta | Basadas en kilometraje o tiempo. |
| FUT-003 | Programación automática de servicios | Media | Agenda de mantenimientos recurrentes. |
| FUT-004 | Indicadores avanzados | Media | Tableros especializados por rol. |
| FUT-005 | Exportaciones especializadas | Baja | Formatos personalizados para integración. |
| FUT-006 | Integración directa con facturación CFDI | Alta | Timbrado fiscal automático. |
| FUT-007 | Notificaciones por correo | Media | Alertas de estado y recordatorios. |
| FUT-008 | Portal para clientes | Baja | Consulta de historial y cotizaciones. |
| FUT-009 | Aplicación móvil nativa | Baja | iOS y Android para técnicos. |
| FUT-010 | Multi-tenancy | Baja | Soporte para múltiples organizaciones. |
| FUT-011 | Integraciones con telemetría o GPS | Baja | Datos de sensores vehiculares. |

---

## 3. Exclusiones del MVP

Las siguientes funcionalidades **NO** están incluidas en el MVP:

| ID | Exclusión | Justificación |
|---|---|---|
| EXC-001 | Timbrado fiscal CFDI | Requiere integración con PAC externo; fuera del alcance inicial. |
| EXC-002 | Contabilidad completa | Existe software especializado; solo se preparan solicitudes. |
| EXC-003 | Compras y proveedores | No es parte del flujo operativo principal del MVP. |
| EXC-004 | Nómina | Fuera del dominio del sistema de mantenimiento. |
| EXC-005 | Control completo de inventario | Solo se registran refacciones utilizadas, sin gestión de existencias. |
| EXC-006 | Cobranza y conciliación bancaria | Dominio administrativo separado. |
| EXC-007 | Seguimiento GPS en tiempo real | Requiere integración con hardware/telemetría especializada. |
| EXC-008 | Mantenimiento predictivo basado en sensores | Requiere infraestructura IoT y análisis avanzado. |

---

## 4. Límites del Sistema

### 4.1 Límites Funcionales

- El sistema no emite facturas timbradas, solo prepara la información para su emisión.
- El sistema no gestiona inventarios físicos de refacciones en el MVP.
- El sistema no realiza cobros ni conciliaciones bancarias.
- El sistema no se integra con dispositivos GPS o sensores en el MVP.

### 4.2 Límites Técnicos

- Base de datos: MySQL 8.0+.
- Backend: PHP 8.2+, Laravel 12.
- Frontend: Vue 3, TypeScript, Inertia.js.
- No se requiere infraestructura de mensajería compleja en el MVP.
- Almacenamiento de archivos: Local o S3-compatible.

### 4.3 Límites Organizacionales

- Usuarios internos únicamente (no hay portal de clientes en MVP).
- Una sola organización (sin multi-tenancy en MVP).
- Zona horaria: `America/Mexico_City`.
- Moneda: MXN.
- Idioma: Español.

---

## 5. Entregables del MVP

| Entregable | Descripción |
|---|---|
| Código fuente | Repositorio Git con todo el código del sistema. |
| Base de datos | Script de migración completo. |
| Documentación técnica | README, instrucciones de despliegue, ADRs. |
| Documentación funcional | Especificaciones en carpeta `specs/`. |
| Pruebas automatizadas | Tests unitarios, de integración y E2E críticos. |
| Manual de usuario | Guía básica de operación por módulo. |

---

## 6. Criterios de Aceptación del Alcance

El MVP se considera completo cuando:

- [ ] Todos los módulos MOD-001 a MOD-014 están implementados.
- [ ] Las pruebas automatizadas cubren ≥ 80% de la lógica crítica.
- [ ] La documentación funcional y técnica está aprobada.
- [ ] Los usuarios clave han validado los flujos principales.
- [ ] El sistema está desplegado en un entorno de producción o staging.
- [ ] No hay incidencias críticas abiertas.

---

## 7. Trazabilidad con Documento Maestro

| Sección | Referencia en Plan Maestro |
|---|---|
| Módulos MVP | Sección 4.1 |
| Funciones futuras | Sección 4.2 |
| Exclusiones | Sección 4.3 |

---

## 8. Control de Cambios

| Versión | Fecha | Cambio | Responsable |
|---|---|---|---|
| 1.0.0 | Pendiente | Especificación inicial | Pendiente |

---

## 9. Aprobaciones Requeridas

- [ ] Responsable operativo.
- [ ] Responsable administrativo.
- [ ] Responsable técnico.
- [ ] Patrocinador o propietario del producto.
