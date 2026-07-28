# PLAN DE DESARROLLO SDD — SISTEMA DE MANTENIMIENTO PREVENTIVO PARA UNIDADES

> **Archivo:** `PLAN_DESARROLLO_SDD_MANTENIMIENTO_UNIDADES.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación maestra propuesta  
> **Metodología:** Specification-Driven Development (SDD)  
> **Stack:** PHP 8.2+, Laravel 12, Jetstream, Inertia.js, Vue 3, TypeScript, MySQL, Vite y Ziggy

---

## 1. Propósito del documento

Este documento define la especificación maestra, la arquitectura de referencia y el roadmap de implementación del **Sistema de mantenimiento preventivo para unidades**.

Su finalidad es permitir que el equipo de desarrollo implemente el producto sin tomar decisiones funcionales o arquitectónicas relevantes fuera de las especificaciones aprobadas.

El plan toma como base los objetivos, problemática, alcance y módulos derivados de `Proyecto.pdf`, conforme al resumen proporcionado. Los detalles técnicos, reglas de negocio, criterios de aceptación y estructuras de datos incluidos en este documento se consideran propuestas de implementación y deberán validarse con los responsables del proceso.

---

## 2. Control del documento

| Campo | Valor |
|---|---|
| Producto | Sistema de mantenimiento preventivo para unidades |
| Tipo | Aplicación web interna |
| Alcance inicial | MVP operativo y administrativo |
| Arquitectura | Monolito modular Laravel + Inertia |
| Base de datos | MySQL |
| Multi-tenancy | Fuera del alcance inicial |
| Facturación CFDI | Solo solicitudes; sin timbrado en MVP |
| Idioma de interfaz | Español |
| Zona horaria inicial | `America/Mexico_City` |
| Moneda inicial | MXN |

### 2.1 Historial de versiones

| Versión | Fecha | Cambio | Responsable |
|---|---|---|---|
| 1.0.0 | Pendiente | Especificación inicial | Pendiente |

### 2.2 Aprobaciones requeridas

- Responsable operativo.
- Responsable administrativo.
- Responsable técnico.
- Patrocinador o propietario del producto.

---

## 3. Visión del producto

### 3.1 Problema

La organización administra información de clientes, unidades, mantenimientos, cotizaciones y solicitudes de facturación mediante registros manuales o fuentes dispersas. Esto provoca:

- Dificultad para localizar mantenimientos anteriores.
- Retrasos en la elaboración de cotizaciones.
- Recaptura de información administrativa y fiscal.
- Duplicidad o pérdida de datos.
- Falta de trazabilidad sobre cambios y responsables.
- Dificultad para conocer el estado actual de cada servicio.
- Limitaciones para generar reportes confiables.

### 3.2 Solución propuesta

Desarrollar una aplicación web centralizada que permita:

- Registrar clientes y sus unidades.
- Crear expedientes digitales por unidad.
- Controlar mantenimientos preventivos y correctivos.
- Registrar servicios, mano de obra, refacciones y evidencias.
- Elaborar cotizaciones.
- Preparar solicitudes de facturación.
- Consultar historiales, indicadores y reportes.
- Mantener trazabilidad de las operaciones críticas.

### 3.3 Objetivo general

Desarrollar una aplicación web que centralice la información de clientes, unidades, mantenimientos, cotizaciones y solicitudes de facturación, mejorando la eficiencia operativa, la trazabilidad y la confiabilidad de los datos.

### 3.4 Objetivos específicos

1. Registrar clientes y unidades.
2. Identificar unidades mediante placas, marca, modelo, año, VIN y número económico.
3. Mantener el historial completo de servicios.
4. Registrar trabajos, refacciones y observaciones técnicas.
5. Generar cotizaciones con mayor rapidez.
6. Gestionar solicitudes de facturación.
7. Mejorar la consulta y trazabilidad de la información.
8. Reducir errores administrativos.
9. Proporcionar información confiable para la toma de decisiones.

### 3.5 Indicadores de éxito

- Reducción del tiempo promedio para localizar el historial de una unidad.
- Reducción del tiempo de elaboración de cotizaciones.
- Disminución de registros duplicados.
- Porcentaje de órdenes con trazabilidad completa.
- Porcentaje de solicitudes de facturación sin recaptura de datos.
- Porcentaje de pruebas críticas automatizadas.

---

## 4. Alcance

### 4.1 Módulos incluidos en el MVP

1. Autenticación y administración de usuarios.
2. Clientes.
3. Unidades de carga pesada.
4. Catálogo de servicios.
5. Registro de mantenimientos preventivos y correctivos.
6. Historial y expediente digital de cada unidad.
7. Refacciones y conceptos utilizados en los servicios.
8. Cotizaciones.
9. Solicitudes de facturación.
10. Consultas, filtros y reportes administrativos.
11. Panel principal con indicadores.
12. Auditoría básica y trazabilidad.
13. Archivos adjuntos y evidencias.
14. Configuración general mínima.

### 4.2 Funciones posteriores al MVP

- Inventario completo de refacciones.
- Existencias, almacenes, entradas, salidas y kardex.
- Alertas de próximos mantenimientos.
- Programación automática de servicios.
- Indicadores avanzados.
- Exportaciones especializadas.
- Integración directa con facturación CFDI.
- Notificaciones por correo.
- Portal para clientes.
- Aplicación móvil nativa.
- Multi-tenancy.
- Integraciones con telemetría o GPS.

### 4.3 Exclusiones del MVP

- Timbrado fiscal CFDI.
- Contabilidad completa.
- Compras y proveedores.
- Nómina.
- Control completo de inventario.
- Cobranza y conciliación bancaria.
- Seguimiento GPS en tiempo real.
- Mantenimiento predictivo basado en sensores.

---

## 5. Principios SDD

El desarrollo seguirá el orden:

1. Requerimiento.
2. Regla de negocio.
3. Modelo de dominio.
4. Contrato de entrada y salida.
5. Criterio de aceptación.
6. Prueba automatizada.
7. Implementación.
8. Validación.
9. Documentación.

Ninguna funcionalidad deberá implementarse sin:

- Identificador de requerimiento.
- Especificación funcional.
- Reglas de negocio aplicables.
- Criterios de aceptación.
- Estrategia de autorización.
- Pruebas previstas.
- Trazabilidad hacia un objetivo del producto.

### 5.1 Estructura recomendada del repositorio de especificaciones

```text
specs/
├── 00-vision.md
├── 01-scope.md
├── 02-glossary.md
├── 03-actors-permissions.md
├── 04-functional-requirements.md
├── 05-business-rules.md
├── 06-non-functional-requirements.md
├── 07-domain-model.md
├── 08-acceptance-criteria.md
├── 09-test-strategy.md
├── 10-deployment.md
├── traceability-matrix.md
├── modules/
│   ├── customers.md
│   ├── vehicles.md
│   ├── service-catalog.md
│   ├── maintenance-orders.md
│   ├── quotations.md
│   ├── billing-requests.md
│   └── reports.md
└── adr/
    ├── ADR-001-monolith-inertia.md
    ├── ADR-002-authentication-authorization.md
    ├── ADR-003-money-calculations.md
    ├── ADR-004-file-storage.md
    └── ADR-005-document-generation.md
```

---

## 6. Glosario del dominio

| Término | Definición |
|---|---|
| Cliente | Persona física o moral propietaria o responsable de una o más unidades. |
| Unidad | Vehículo registrado en el sistema y asociado a un cliente activo. |
| Número económico | Identificador interno asignado a una unidad por el cliente o la empresa. |
| VIN | Número de identificación vehicular. |
| Servicio | Trabajo normalizado que puede incluirse en una orden o cotización. |
| Mantenimiento preventivo | Intervención planificada para reducir la probabilidad de falla. |
| Mantenimiento correctivo | Intervención realizada para corregir una falla detectada. |
| Orden de mantenimiento | Registro operativo que controla una intervención desde la recepción hasta la entrega. |
| Refacción | Material, pieza o componente utilizado durante un servicio. |
| Cotización | Propuesta económica emitida para servicios, mano de obra o refacciones. |
| Solicitud de facturación | Expediente administrativo que concentra la información necesaria para emitir una factura. |
| Evidencia | Fotografía, documento o archivo relacionado con un cliente, unidad, orden, cotización o solicitud. |
| Folio | Identificador legible y único generado por el sistema. |
| Estado | Etapa vigente dentro del flujo de un registro. |

---

## 7. Actores, roles y permisos

### 7.1 Roles iniciales

#### Administrador

- Gestionar usuarios, roles y permisos.
- Acceder a todos los módulos.
- Configurar catálogos y parámetros.
- Consultar reportes.
- Administrar respaldos y configuraciones operativas.
- Autorizar cambios críticos.

#### Personal administrativo

- Gestionar clientes.
- Gestionar unidades.
- Elaborar cotizaciones.
- Crear solicitudes de facturación.
- Consultar servicios e historiales.
- Generar reportes administrativos.

#### Personal técnico

- Consultar clientes y unidades.
- Registrar inspecciones y diagnósticos.
- Registrar mantenimientos.
- Agregar trabajos, refacciones y observaciones.
- Adjuntar evidencias.
- Actualizar estados operativos permitidos.

#### Consulta o supervisión

- Visualizar clientes, unidades, mantenimientos, cotizaciones y reportes.
- No modificar información crítica.

### 7.2 Matriz resumida de permisos

| Acción | Administrador | Administrativo | Técnico | Consulta |
|---|:---:|:---:|:---:|:---:|
| Gestionar usuarios | Sí | No | No | No |
| Gestionar roles y permisos | Sí | No | No | No |
| Crear y editar clientes | Sí | Sí | No | No |
| Consultar clientes | Sí | Sí | Sí | Sí |
| Crear y editar unidades | Sí | Sí | No | No |
| Consultar unidades | Sí | Sí | Sí | Sí |
| Crear órdenes | Sí | Sí | Sí | No |
| Registrar diagnóstico | Sí | No | Sí | No |
| Agregar servicios y refacciones | Sí | Limitado | Sí | No |
| Cambiar estado de orden | Sí | Según transición | Según transición | No |
| Reabrir orden terminada | Sí | No | No | No |
| Crear cotizaciones | Sí | Sí | Limitado | No |
| Autorizar descuentos | Sí | Según límite | No | No |
| Crear solicitudes de facturación | Sí | Sí | No | No |
| Consultar reportes | Sí | Sí | Limitado | Sí |
| Exportar información | Sí | Sí | Limitado | Según permiso |
| Ver auditoría | Sí | Limitado | No | No |

### 7.3 Implementación de autorización

- Policies por recurso.
- Gates para capacidades transversales.
- Permisos almacenados en base de datos.
- Middleware para acceso a módulos.
- Verificación de permisos en backend como fuente de verdad.
- Ocultamiento de acciones no autorizadas en frontend.
- Registro de intentos sensibles denegados cuando corresponda.

---

## 8. Arquitectura técnica

### 8.1 Estilo arquitectónico

Se utilizará un **monolito modular** basado en Laravel e Inertia.js.

Ventajas:

- Menor complejidad operativa.
- Autenticación y autorización centralizadas.
- Transacciones consistentes.
- Despliegue simplificado.
- Evolución futura hacia servicios separados si el volumen lo requiere.

### 8.2 Backend Laravel

- Controllers delgados.
- Form Requests para validación.
- Policies para autorización.
- Services para procesos de negocio con múltiples pasos.
- Actions para casos de uso enfocados.
- Eloquent para persistencia.
- Transacciones para operaciones críticas.
- Events y Listeners para efectos secundarios desacoplados.
- Jobs y Queues para generación de documentos o tareas lentas.
- DTOs cuando reduzcan ambigüedad entre capas.
- Resources para transformar datos cuando sea necesario.
- Auditoría de cambios críticos.

### 8.3 Frontend

- Inertia.js como puente entre Laravel y Vue.
- Vue 3 con Composition API.
- TypeScript.
- `<script setup lang="ts">`.
- `useForm` para mutaciones.
- Ziggy para generación de rutas.
- Páginas como orquestadores.
- Componentes reutilizables para formularios, tablas, filtros, estados y diálogos.
- Estados explícitos de carga, error y ausencia de información.
- Diseño adaptable para escritorio, tableta y móvil.

### 8.4 Base de datos

- MySQL como base principal.
- Tablas normalizadas.
- Claves foráneas explícitas.
- Índices para filtros frecuentes.
- Restricciones de unicidad.
- Campos monetarios con `decimal`.
- Fechas en UTC a nivel de persistencia y presentación en zona horaria de negocio.
- Eliminación lógica solo cuando exista requisito de recuperación o auditoría.

### 8.5 Organización sugerida del código

```text
app/
├── Actions/
│   ├── Customers/
│   ├── Vehicles/
│   ├── Maintenance/
│   ├── Quotations/
│   └── Billing/
├── DTOs/
├── Enums/
├── Events/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Jobs/
├── Listeners/
├── Models/
├── Policies/
├── Services/
└── Support/

resources/js/
├── Components/
│   ├── Common/
│   ├── Forms/
│   ├── Tables/
│   ├── Filters/
│   └── Status/
├── Composables/
├── Layouts/
├── Pages/
│   ├── Customers/
│   ├── Vehicles/
│   ├── MaintenanceOrders/
│   ├── Quotations/
│   ├── BillingRequests/
│   └── Reports/
├── Types/
└── Utils/
```

---

## 9. Modelo de datos preliminar

### 9.1 Entidades mínimas

- `users`
- `roles`
- `permissions`
- `role_user` o tablas equivalentes del paquete seleccionado
- `customers`
- `customer_fiscal_profiles`
- `vehicles`
- `vehicle_types`
- `service_categories`
- `service_catalog`
- `part_catalog`
- `maintenance_orders`
- `maintenance_order_items`
- `maintenance_parts`
- `maintenance_status_history`
- `quotations`
- `quotation_items`
- `quotation_status_history`
- `billing_requests`
- `billing_request_items`
- `billing_request_status_history`
- `attachments`
- `notes`
- `activity_logs`
- `settings`
- `document_sequences`

### 9.2 Relaciones principales

- Un cliente tiene muchas unidades.
- Una unidad pertenece a un cliente.
- Una unidad tiene muchas órdenes de mantenimiento.
- Una orden tiene múltiples servicios, trabajos y refacciones.
- Una cotización pertenece a un cliente y una unidad.
- Una cotización puede originar una orden de mantenimiento.
- Una solicitud de facturación puede originarse desde una orden o una cotización.
- Los cambios de estado conservan fecha, usuario responsable y observaciones.
- Los archivos adjuntos pueden asociarse de forma polimórfica a diferentes recursos.

### 9.3 Campos clave sugeridos

#### `customers`

- `id`
- `type`: `individual` o `company`
- `name`
- `trade_name`
- `phone`
- `email`
- `status`
- `created_by`
- `updated_by`
- timestamps

#### `customer_fiscal_profiles`

- `id`
- `customer_id`
- `legal_name`
- `rfc`
- `tax_regime_code`
- `cfdi_use_code`
- `postal_code`
- `email`
- `is_default`
- timestamps

#### `vehicles`

- `id`
- `customer_id`
- `vehicle_type_id`
- `license_plate`
- `license_plate_normalized`
- `vin`
- `economic_number`
- `brand`
- `model`
- `year`
- `engine_type`
- `current_mileage`
- `status`
- timestamps

#### `maintenance_orders`

- `id`
- `folio`
- `customer_id`
- `vehicle_id`
- `quotation_id`
- `type`: preventivo o correctivo
- `status`
- `received_at`
- `started_at`
- `completed_at`
- `delivered_at`
- `mileage`
- `reason`
- `diagnosis`
- `technical_notes`
- `subtotal`
- `discount_total`
- `tax_total`
- `total`
- `assigned_user_id`
- `created_by`
- `updated_by`
- timestamps

#### `quotations`

- `id`
- `folio`
- `version`
- `customer_id`
- `vehicle_id`
- `status`
- `issued_at`
- `valid_until`
- `subtotal`
- `discount_total`
- `tax_total`
- `total`
- `commercial_terms`
- `accepted_at`
- `accepted_by`
- timestamps

#### `billing_requests`

- `id`
- `folio`
- `customer_id`
- `vehicle_id`
- `maintenance_order_id`
- `quotation_id`
- `fiscal_profile_snapshot`
- `status`
- `payment_method_code`
- `payment_form_code`
- `currency`
- `subtotal`
- `tax_total`
- `total`
- `invoice_reference`
- `requested_by`
- `processed_by`
- timestamps

### 9.4 Índices mínimos

- `customers(name)`.
- `customers(email)` cuando sea relevante.
- `vehicles(customer_id, status)`.
- `vehicles(license_plate_normalized)`.
- `vehicles(vin)` único cuando no sea nulo.
- `vehicles(economic_number)` según alcance de unicidad acordado.
- `maintenance_orders(vehicle_id, received_at)`.
- `maintenance_orders(status, received_at)`.
- `quotations(status, issued_at)`.
- `billing_requests(status, created_at)`.
- Índices para todas las claves foráneas.

---

## 10. Reglas de negocio generales

### RN-GEN-001 — Folios

Los folios deben ser únicos por tipo documental y generarse en servidor mediante una secuencia transaccional.

### RN-GEN-002 — Importes

Todos los importes se recalcularán en servidor. Los valores enviados por el navegador no serán fuente de verdad.

### RN-GEN-003 — Historial financiero

Las cotizaciones, órdenes y solicitudes conservarán instantáneas de precio, impuestos, descuentos y datos fiscales utilizados al momento de su emisión.

### RN-GEN-004 — Auditoría

Los cambios críticos registrarán usuario, fecha, recurso, acción, valores anteriores y valores posteriores cuando sea razonable.

### RN-GEN-005 — Eliminación

Los registros con impacto histórico no se eliminarán físicamente. Se cancelarán, desactivarán o archivarán según el módulo.

### RN-GEN-006 — Archivos

Los archivos deberán validarse por tipo, tamaño y extensión. El sistema no confiará únicamente en el nombre del archivo.

### RN-GEN-007 — Fechas

Las fechas de negocio deberán seguir una secuencia válida; por ejemplo, una entrega no puede preceder a la terminación.

### RN-GEN-008 — Estados

Las transiciones de estado serán explícitas y validadas en backend.

### RN-GEN-009 — Concurrencia

Las operaciones de aprobación, folios, cambios de estado críticos y generación de solicitudes usarán transacciones y controles de concurrencia cuando aplique.

---

## 11. Especificaciones por módulo

## 11.1 Autenticación, usuarios y seguridad

### Requerimientos

- `RF-AUT-001`: iniciar sesión.
- `RF-AUT-002`: cerrar sesión.
- `RF-AUT-003`: recuperar contraseña.
- `RF-AUT-004`: administrar perfil.
- `RF-AUT-005`: administrar usuarios.
- `RF-AUT-006`: asignar roles y permisos.
- `RF-AUT-007`: registrar accesos y operaciones sensibles.

### Criterios de aceptación

```gherkin
Dado un usuario activo con credenciales válidas
Cuando inicia sesión
Entonces el sistema crea una sesión segura
Y muestra únicamente los módulos autorizados.
```

```gherkin
Dado un usuario sin permiso para administrar clientes
Cuando intenta acceder directamente a la ruta de edición
Entonces el servidor responde con acceso denegado
Y no modifica información.
```

---

## 11.2 Clientes

### Requerimientos

- `RF-CLI-001`: registrar cliente.
- `RF-CLI-002`: consultar cliente.
- `RF-CLI-003`: editar cliente.
- `RF-CLI-004`: desactivar cliente.
- `RF-CLI-005`: administrar perfil fiscal.
- `RF-CLI-006`: buscar y filtrar clientes.
- `RF-CLI-007`: consultar unidades y documentos relacionados.

### Datos funcionales

- Persona física o moral.
- Nombre o razón social.
- Nombre comercial.
- Teléfono.
- Correo.
- Domicilio.
- Datos fiscales.
- Estado.

### Reglas

- `RN-CLI-001`: evitar duplicados mediante RFC, correo, teléfono o combinación de nombre y datos de contacto, según reglas aprobadas.
- `RN-CLI-002`: un cliente con historial no podrá eliminarse físicamente.
- `RN-CLI-003`: solo clientes activos podrán recibir nuevas unidades u operaciones.
- `RN-CLI-004`: los datos fiscales usados en documentos se conservarán como instantánea.

### Criterio de aceptación principal

El usuario autorizado debe localizar un cliente y visualizar sus unidades, servicios, cotizaciones y solicitudes relacionadas desde una sola vista.

---

## 11.3 Unidades

### Requerimientos

- `RF-UNI-001`: registrar unidad.
- `RF-UNI-002`: editar unidad.
- `RF-UNI-003`: consultar expediente.
- `RF-UNI-004`: adjuntar documentos y fotografías.
- `RF-UNI-005`: buscar por placas, VIN, cliente, marca, modelo o número económico.
- `RF-UNI-006`: consultar historial cronológico.

### Reglas

- `RN-UNI-001`: las placas serán obligatorias salvo excepción documentada.
- `RN-UNI-002`: las placas se normalizarán para búsqueda y comparación.
- `RN-UNI-003`: el VIN será único cuando se proporcione.
- `RN-UNI-004`: el año deberá estar dentro de un rango válido configurable.
- `RN-UNI-005`: cada unidad pertenecerá a un solo cliente activo.
- `RN-UNI-006`: el cambio de propietario deberá conservar trazabilidad histórica si se habilita.

### Criterio de aceptación principal

La consulta de una unidad mostrará identificación, propietario, evidencias e historial cronológico completo.

---

## 11.4 Catálogos de servicios y conceptos

### Requerimientos

- `RF-CAT-001`: administrar categorías.
- `RF-CAT-002`: administrar servicios preventivos y correctivos.
- `RF-CAT-003`: definir unidad de medida.
- `RF-CAT-004`: definir precio de referencia.
- `RF-CAT-005`: definir tiempo estimado.
- `RF-CAT-006`: activar o desactivar conceptos.
- `RF-CAT-007`: administrar catálogo básico de refacciones.

### Reglas

- Los conceptos inactivos no podrán agregarse a nuevos documentos.
- Los documentos históricos conservarán descripción y precio originales.
- Podrán capturarse conceptos extraordinarios cuando el usuario cuente con permiso.

---

## 11.5 Órdenes de mantenimiento

### Flujo de estados

1. Recepción.
2. Inspección.
3. Diagnóstico.
4. Cotización.
5. Autorización.
6. En proceso.
7. Terminado.
8. Entregado.
9. Cancelado.

### Requerimientos

- `RF-MAN-001`: crear orden.
- `RF-MAN-002`: registrar kilometraje y fecha.
- `RF-MAN-003`: capturar motivo de ingreso.
- `RF-MAN-004`: registrar inspección y diagnóstico.
- `RF-MAN-005`: agregar servicios y mano de obra.
- `RF-MAN-006`: agregar refacciones.
- `RF-MAN-007`: adjuntar evidencias.
- `RF-MAN-008`: asignar responsable.
- `RF-MAN-009`: cambiar estado.
- `RF-MAN-010`: registrar fechas de inicio, terminación y entrega.
- `RF-MAN-011`: calcular importes.
- `RF-MAN-012`: consultar historial de estados.

### Reglas

- `RN-MAN-001`: cada cambio de estado quedará auditado.
- `RN-MAN-002`: las transiciones no autorizadas serán rechazadas.
- `RN-MAN-003`: una orden terminada no se modificará sin permiso de reapertura.
- `RN-MAN-004`: los importes se recalcularán en servidor.
- `RN-MAN-005`: la unidad y el cliente de la orden deberán ser consistentes.
- `RN-MAN-006`: la terminación requerirá diagnóstico, servicios realizados y responsable.
- `RN-MAN-007`: la entrega requerirá que la orden esté terminada.
- `RN-MAN-008`: la cancelación requerirá motivo.

### Criterio de aceptación principal

Cada mantenimiento será rastreable desde la recepción hasta la entrega, incluyendo responsables, tiempos, trabajos, refacciones, documentos y cambios de estado.

---

## 11.6 Historial de mantenimiento

### Requerimientos

- `RF-HIS-001`: mostrar línea de tiempo por unidad.
- `RF-HIS-002`: filtrar por fecha y tipo.
- `RF-HIS-003`: mostrar servicios y refacciones.
- `RF-HIS-004`: mostrar fallas y observaciones.
- `RF-HIS-005`: mostrar cotizaciones y solicitudes relacionadas.
- `RF-HIS-006`: consultar documentos y fotografías.
- `RF-HIS-007`: exportar expediente a PDF.

### Criterio de aceptación principal

El usuario autorizado podrá identificar qué se hizo, cuándo, por quién, con qué materiales y bajo qué documento económico.

---

## 11.7 Cotizaciones

### Estados

- Borrador.
- Enviada.
- Aceptada.
- Rechazada.
- Vencida.
- Cancelada.

### Requerimientos

- `RF-COT-001`: crear cotización.
- `RF-COT-002`: seleccionar cliente y unidad.
- `RF-COT-003`: agregar servicios, mano de obra y refacciones.
- `RF-COT-004`: aplicar descuentos autorizados.
- `RF-COT-005`: calcular subtotal, impuestos y total.
- `RF-COT-006`: definir vigencia y condiciones.
- `RF-COT-007`: generar folio.
- `RF-COT-008`: exportar PDF.
- `RF-COT-009`: registrar aceptación o rechazo.
- `RF-COT-010`: duplicar o versionar.
- `RF-COT-011`: relacionar una cotización aceptada con una orden.

### Reglas

- `RN-COT-001`: la cotización conservará importes históricos.
- `RN-COT-002`: una cotización enviada no se editará; se generará una nueva versión.
- `RN-COT-003`: el descuento requerirá permiso y podrá estar sujeto a límites.
- `RN-COT-004`: una cotización vencida no podrá aceptarse sin reactivación o nueva versión.
- `RN-COT-005`: solo una versión podrá marcarse como aceptada dentro de la misma propuesta.

### Criterio de aceptación principal

Los cálculos almacenados, mostrados y exportados deben coincidir exactamente, respetando redondeos y configuración fiscal.

---

## 11.8 Solicitudes de facturación

### Estados propuestos

- Borrador.
- Pendiente de revisión.
- Incompleta.
- Aprobada.
- Procesada.
- Rechazada.
- Cancelada.

### Requerimientos

- `RF-FAC-001`: crear solicitud desde orden o cotización.
- `RF-FAC-002`: recuperar datos fiscales.
- `RF-FAC-003`: seleccionar conceptos.
- `RF-FAC-004`: calcular importes.
- `RF-FAC-005`: registrar método y forma de pago.
- `RF-FAC-006`: adjuntar documentación.
- `RF-FAC-007`: controlar estados.
- `RF-FAC-008`: agregar observaciones.
- `RF-FAC-009`: registrar referencia de factura emitida.

### Reglas

- `RN-FAC-001`: la solicitud guardará una instantánea de los datos fiscales.
- `RN-FAC-002`: una solicitud no podrá enviarse a revisión si faltan datos obligatorios.
- `RN-FAC-003`: los importes deberán coincidir con los conceptos incluidos.
- `RN-FAC-004`: una solicitud procesada no podrá modificarse.
- `RN-FAC-005`: el timbrado CFDI está fuera del alcance inicial.

### Criterio de aceptación principal

La solicitud contendrá toda la información necesaria para procesar la factura sin recapturar los datos básicos del cliente, unidad y servicios.

---

## 11.9 Dashboard, búsquedas y reportes

### Indicadores iniciales

- Unidades atendidas.
- Servicios abiertos.
- Servicios terminados.
- Cotizaciones pendientes.
- Cotizaciones aceptadas.
- Solicitudes de facturación pendientes.
- Mantenimientos por periodo.
- Unidades con mayor frecuencia de servicio.

### Reportes

- Historial por unidad.
- Servicios por cliente.
- Mantenimientos por rango de fechas.
- Cotizaciones por estado.
- Solicitudes de facturación por estado.
- Refacciones o conceptos más utilizados.

### Reglas

- Los indicadores deben respetar permisos.
- Los filtros deberán conservarse en la URL cuando sea útil.
- Los listados usarán paginación en servidor.
- Las búsquedas frecuentes deberán usar columnas indexadas.
- Las exportaciones extensas podrán ejecutarse mediante Jobs.

---

## 12. Historias de usuario prioritarias

### HU-CLI-01 — Registrar cliente

**Como** personal administrativo  
**Quiero** registrar un cliente  
**Para** asociar sus unidades, servicios y documentos.

### HU-UNI-01 — Registrar unidad

**Como** personal administrativo  
**Quiero** registrar una unidad para un cliente  
**Para** crear su expediente digital.

### HU-MAN-01 — Crear orden

**Como** técnico o administrativo autorizado  
**Quiero** crear una orden de mantenimiento  
**Para** controlar la intervención desde la recepción hasta la entrega.

### HU-HIS-01 — Consultar historial

**Como** usuario autorizado  
**Quiero** consultar el historial de una unidad  
**Para** conocer fallas, trabajos y refacciones anteriores.

### HU-COT-01 — Crear cotización

**Como** personal administrativo  
**Quiero** elaborar una cotización  
**Para** presentar al cliente una propuesta económica consistente.

### HU-FAC-01 — Solicitar facturación

**Como** personal administrativo  
**Quiero** crear una solicitud de facturación desde una orden  
**Para** evitar la recaptura de información.

---

## 13. Requerimientos no funcionales

### RNF-SEG — Seguridad

- Autenticación obligatoria.
- Autorización en servidor.
- Protección CSRF.
- Rate limiting en operaciones sensibles.
- Sesiones seguras.
- Gestión de secretos fuera del repositorio.
- Validación estricta de archivos.
- Registro de accesos y cambios críticos.

### RNF-REN — Rendimiento

- Paginación en listados.
- Índices para búsquedas frecuentes.
- Evitar consultas N+1.
- Consultas frecuentes con tiempo objetivo inferior a dos segundos bajo carga nominal, sujeto a validación de infraestructura.
- Procesos pesados mediante colas.

### RNF-DIS — Disponibilidad y continuidad

- Respaldos automáticos de base de datos.
- Verificación periódica de restauración.
- Procedimiento documentado de recuperación.
- Monitoreo de errores y espacio de almacenamiento.

### RNF-USA — Usabilidad

- Interfaz en español.
- Diseño adaptable.
- Navegación consistente.
- Mensajes de validación claros.
- Estados de carga, vacío y error.
- Accesibilidad por teclado y etiquetas semánticas.

### RNF-MAN — Mantenibilidad

- Convenciones de código documentadas.
- Pruebas automatizadas.
- Análisis estático.
- Linting y formato.
- Registro de decisiones arquitectónicas.
- Dependencias actualizadas de forma controlada.

### RNF-DAT — Protección de datos

- Acceso de mínimo privilegio.
- Evitar exposición innecesaria de datos fiscales.
- Políticas de retención.
- Registro de exportaciones sensibles cuando corresponda.
- Cumplimiento aplicable con protección de datos personales en México.

---

## 14. Contratos funcionales y manejo de errores

### 14.1 Validación

- Toda mutación usará Form Request.
- Los mensajes de error serán comprensibles.
- Las reglas de unicidad ignorarán correctamente el registro actual al editar.
- Las referencias entre cliente y unidad serán validadas en servidor.

### 14.2 Respuestas y navegación

- Las páginas se renderizarán mediante Inertia.
- Las mutaciones devolverán redirecciones con mensajes `flash`.
- Los errores de validación se mostrarán junto al campo correspondiente.
- Los errores inesperados tendrán una pantalla controlada y un identificador de seguimiento.

### 14.3 Transacciones

Se usarán transacciones en:

- Generación de folios.
- Creación de orden con partidas.
- Aprobación o versionado de cotización.
- Conversión de cotización a orden.
- Creación de solicitud desde una orden.
- Cambios de estado con efectos relacionados.

---

## 15. Estrategia de pruebas

### 15.1 Tipos de prueba

- Unitarias.
- Feature tests.
- Integración.
- Policies y permisos.
- Validación de formularios.
- Relaciones de base de datos.
- Cálculos monetarios.
- Generación de documentos.
- Flujos Inertia.
- Pruebas de interfaz.
- Regresión.
- Aceptación de usuario.

### 15.2 Casos críticos

1. Cliente con varias unidades.
2. Unidad con historial extenso.
3. Placas duplicadas después de normalización.
4. VIN duplicado.
5. Cotización con impuestos y descuentos.
6. Intento de modificación de cotización enviada.
7. Cambio de estado no autorizado.
8. Orden terminada sin datos obligatorios.
9. Solicitud de facturación incompleta.
10. Acceso a información sin permisos.
11. Fallo durante una operación transaccional.
12. Generación concurrente de folios.
13. Archivo con extensión permitida pero contenido inválido.

### 15.3 Pirámide recomendada

- Mayoría: pruebas unitarias y feature.
- Intermedio: integración.
- Menor cantidad: pruebas end-to-end de flujos críticos.

### 15.4 Cobertura mínima de aceptación

Todos los criterios críticos deberán estar automatizados y ejecutarse en integración continua antes de fusionar cambios.

---

## 16. Interfaz y experiencia de usuario

### 16.1 Principios

- Claridad operativa.
- Consistencia visual.
- Acciones principales visibles.
- Prevención de errores.
- Confirmación para operaciones destructivas.
- Retroalimentación inmediata.

### 16.2 Componentes mínimos

- Layout autenticado.
- Menú por permisos.
- Breadcrumbs.
- Tablas con búsqueda, filtros y paginación.
- Formularios tipados.
- Selectores reutilizables de cliente y unidad.
- Chips de estado.
- Timeline de mantenimiento.
- Visor de adjuntos.
- Diálogos de confirmación.
- Tarjetas KPI.
- Estado vacío con acción sugerida.

### 16.3 Vistas principales

- Dashboard.
- Listado y detalle de clientes.
- Listado y expediente de unidades.
- Listado y detalle de órdenes.
- Constructor de cotizaciones.
- Solicitudes de facturación.
- Reportes.
- Usuarios, roles y permisos.
- Configuración.

---

## 17. Migración y limpieza de información

### Actividades

1. Identificar fuentes existentes.
2. Crear plantilla de importación.
3. Normalizar clientes y unidades.
4. Detectar duplicados.
5. Validar placas, VIN y fechas.
6. Ejecutar importación de prueba.
7. Elaborar reporte de inconsistencias.
8. Corregir datos.
9. Aprobar importación definitiva.
10. Conservar referencia a la fuente original.

### Reglas

- No se crearán duplicados silenciosos.
- Los registros rechazados se incluirán en un reporte.
- La importación será repetible o contará con mecanismo de idempotencia.
- Se realizará respaldo antes de la carga definitiva.

---

## 18. Seguridad, respaldos y continuidad

### Controles

- Política de contraseñas.
- Mínimo privilegio.
- HTTPS obligatorio en producción.
- Cookies seguras.
- Validación MIME y tamaño de archivos.
- Almacenamiento privado de evidencias sensibles.
- Logs de auditoría.
- Respaldo automático de MySQL.
- Respaldo de archivos.
- Pruebas de restauración.
- Rotación de logs.
- Gestión segura de secretos.

### Objetivos de recuperación propuestos

- `RPO`: máximo 24 horas para MVP, sujeto a criticidad real.
- `RTO`: máximo 8 horas para MVP, sujeto a infraestructura y contrato operativo.

---

## 19. Despliegue

### Ambientes

- Desarrollo.
- Pruebas.
- Producción.

### Requisitos

- Servidor Linux compatible.
- PHP 8.2 o superior.
- MySQL.
- Node.js para compilación.
- Nginx o Apache.
- HTTPS.
- Supervisor o equivalente para colas.
- Cron para Scheduler.
- Almacenamiento persistente.

### Flujo de despliegue

1. Validar pruebas.
2. Crear respaldo.
3. Activar modo de mantenimiento cuando corresponda.
4. Descargar versión.
5. Instalar dependencias PHP.
6. Instalar y compilar frontend.
7. Ejecutar migraciones seguras.
8. Limpiar y reconstruir cachés.
9. Reiniciar colas.
10. Ejecutar smoke tests.
11. Desactivar mantenimiento.
12. Monitorear errores.

### Rollback

- Reversión de código.
- Restauración de base cuando una migración no sea reversible de forma segura.
- Conservación de artefactos de la versión anterior.
- Procedimiento documentado y probado.

---

## 20. Roadmap por hitos

## Hito 0 — Validación del alcance

**Objetivo:** convertir el contenido académico y el resumen del PDF en requisitos verificables.

**Actividades:**

- Identificar actores.
- Documentar proceso actual.
- Definir límites del MVP.
- Construir glosario.
- Clasificar funciones obligatorias y posteriores.
- Resolver términos del dominio.

**Entregables:**

- Visión del producto.
- Alcance aprobado.
- Mapa de actores.
- Glosario.
- Lista priorizada de requerimientos.
- Matriz de trazabilidad inicial.

**Criterio de aceptación:**

Todos los objetivos específicos estarán vinculados con al menos un módulo y un criterio de aceptación.

---

## Hito 1 — Especificación SDD inicial

**Objetivo:** producir las especificaciones antes de implementar.

**Entregables:**

- `specs/00-vision.md`
- `specs/01-requirements.md`
- `specs/02-business-rules.md`
- `specs/03-non-functional-requirements.md`
- `specs/04-acceptance-criteria.md`
- `specs/traceability-matrix.md`

**Criterio de aceptación:**

Ningún módulo podrá desarrollarse sin especificación, reglas y criterios de aceptación.

---

## Hito 2 — Diseño de arquitectura

**Objetivo:** establecer la estructura técnica.

**Entregables:**

- Diagrama de arquitectura.
- Mapa de módulos.
- Convenciones de código.
- ADR de Laravel, Inertia, Vue y MySQL.
- ADR de autenticación y autorización.
- ADR de generación de documentos.

**Criterio de aceptación:**

La arquitectura permitirá agregar inventario, alertas y reportes avanzados sin reestructurar los módulos principales.

---

## Hito 3 — Diseño de base de datos

**Objetivo:** construir un modelo relacional consistente.

**Entregables:**

- Diagrama ER.
- Diccionario de datos.
- Migraciones.
- Factories.
- Seeders.
- Datos iniciales de prueba.

**Criterio de aceptación:**

Las migraciones se ejecutarán desde una base vacía y reconstruirán el entorno de forma reproducible.

---

## Hito 4 — Inicialización del proyecto

**Objetivo:** preparar el entorno base.

**Actividades:**

- Crear Laravel.
- Configurar Jetstream con Inertia y Vue.
- Activar TypeScript.
- Configurar MySQL, Vite y Ziggy.
- Configurar `.env.example`.
- Establecer linting, formato, análisis estático y CI.
- Definir flujo Git.

**Criterio de aceptación:**

Un desarrollador podrá clonar, instalar, migrar y ejecutar el sistema usando solo el README.

---

## Hito 5 — Autenticación, usuarios y seguridad

**Objetivo:** controlar el acceso.

**Entregables:**

- Módulo de usuarios.
- Matriz de permisos.
- Policies.
- Pruebas de autorización.
- Auditoría inicial.

**Criterio de aceptación:**

Cada actor solo accederá a las funciones autorizadas.

---

## Hito 6 — Clientes

**Objetivo:** centralizar datos generales y fiscales.

**Criterio de aceptación:**

El usuario localizará un cliente y visualizará sus unidades y operaciones relacionadas.

---

## Hito 7 — Unidades

**Objetivo:** crear el expediente digital por vehículo.

**Criterio de aceptación:**

La vista de unidad mostrará identificación, propietario e historial cronológico.

---

## Hito 8 — Catálogos

**Objetivo:** normalizar servicios, categorías y conceptos.

**Criterio de aceptación:**

Órdenes y cotizaciones reutilizarán conceptos normalizados y conservarán sus instantáneas históricas.

---

## Hito 9 — Órdenes de mantenimiento

**Objetivo:** controlar cada intervención.

**Criterio de aceptación:**

Cada orden será rastreable desde la recepción hasta la entrega.

---

## Hito 10 — Historial

**Objetivo:** proporcionar consulta inmediata del expediente.

**Criterio de aceptación:**

Se podrá conocer qué se hizo, cuándo, por quién y con qué materiales.

---

## Hito 11 — Cotizaciones

**Objetivo:** agilizar propuestas económicas.

**Criterio de aceptación:**

Los importes del sistema coincidirán con el PDF generado.

---

## Hito 12 — Solicitudes de facturación

**Objetivo:** preparar la información requerida para facturar.

**Criterio de aceptación:**

El área administrativa procesará la solicitud sin recapturar datos básicos.

---

## Hito 13 — Dashboard, búsquedas y reportes

**Objetivo:** apoyar la operación y toma de decisiones.

**Criterio de aceptación:**

Los resultados respetarán permisos, filtros y periodos.

---

## Hito 14 — Interfaz y experiencia de usuario

**Objetivo:** ofrecer una interfaz clara y eficiente.

**Criterio de aceptación:**

Los flujos principales se completarán sin ambigüedad y con retroalimentación visible.

---

## Hito 15 — Pruebas y calidad

**Objetivo:** verificar funcionalidad, seguridad e integridad.

**Criterio de aceptación:**

Todos los escenarios críticos se ejecutarán correctamente en CI.

---

## Hito 16 — Migración de información

**Objetivo:** incorporar registros históricos.

**Criterio de aceptación:**

Los registros importados conservarán trazabilidad y no producirán duplicados silenciosos.

---

## Hito 17 — Seguridad, respaldos y continuidad

**Objetivo:** proteger la información.

**Criterio de aceptación:**

Existirá un procedimiento probado de restauración de aplicación, base de datos y archivos.

---

## Hito 18 — Despliegue

**Objetivo:** publicar el sistema de manera controlada.

**Criterio de aceptación:**

El despliegue será reproducible, documentado y reversible.

---

## Hito 19 — Validación con usuarios

**Objetivo:** confirmar que el producto resuelve los procesos definidos.

**Escenarios:**

- Registrar cliente.
- Registrar varias unidades.
- Crear orden.
- Registrar servicios y refacciones.
- Consultar historial.
- Crear y aceptar cotización.
- Crear solicitud de facturación.
- Consultar reportes.
- Validar permisos.

**Entregables:**

- Guion de pruebas.
- Evidencias.
- Registro de observaciones.
- Acta de aceptación.
- Backlog de mejoras.

---

## Hito 20 — Documentación y cierre

**Objetivo:** entregar un sistema mantenible.

**Documentación:**

- Instalación.
- Configuración.
- Arquitectura.
- Modelo de datos.
- Rutas y endpoints internos.
- Roles y permisos.
- Manual de usuario.
- Manual administrativo.
- Respaldo y restauración.
- Despliegue.
- Decisiones arquitectónicas.
- Deuda técnica conocida.

**Criterio de aceptación:**

Otro desarrollador podrá mantener el proyecto usando la documentación entregada.

---

## 21. Dependencias entre hitos

```text
H0 Alcance
  └── H1 Especificaciones
      ├── H2 Arquitectura
      └── H3 Base de datos
          └── H4 Inicialización
              └── H5 Seguridad
                  ├── H6 Clientes
                  │   └── H7 Unidades
                  │       ├── H9 Órdenes
                  │       │   └── H10 Historial
                  │       └── H11 Cotizaciones
                  │           └── H12 Facturación
                  └── H8 Catálogos
                      ├── H9 Órdenes
                      └── H11 Cotizaciones

H6-H12 ──> H13 Dashboard y reportes
H5-H14 ──> H15 Pruebas
H6-H12 ──> H16 Migración
H4-H16 ──> H17 Seguridad y continuidad
H15-H17 ──> H18 Despliegue
H18 ──> H19 Validación
H19 ──> H20 Cierre
```

---

## 22. Matriz de trazabilidad inicial

| Objetivo | Requerimiento | Especificación | Módulo | Historia | Criterio | Prueba | Hito |
|---|---|---|---|---|---|---|---|
| Registrar clientes | RF-CLI-001 | SPEC-CLIENTES | Clientes | HU-CLI-01 | CA-CLI-001 | Feature | 6 |
| Registrar unidades | RF-UNI-001 | SPEC-UNIDADES | Unidades | HU-UNI-01 | CA-UNI-001 | Feature | 7 |
| Mantener historial | RF-HIS-001 | SPEC-HISTORIAL | Historial | HU-HIS-01 | CA-HIS-001 | Feature/E2E | 10 |
| Registrar mantenimientos | RF-MAN-001 | SPEC-MANTENIMIENTO | Órdenes | HU-MAN-01 | CA-MAN-001 | Feature/E2E | 9 |
| Generar cotizaciones | RF-COT-001 | SPEC-COTIZACIONES | Cotizaciones | HU-COT-01 | CA-COT-001 | Feature | 11 |
| Gestionar facturación | RF-FAC-001 | SPEC-FACTURACION | Solicitudes | HU-FAC-01 | CA-FAC-001 | Feature | 12 |

La matriz completa deberá mantener la cadena:

```text
Objetivo fuente
→ Requerimiento
→ Regla de negocio
→ Especificación
→ Módulo
→ Historia de usuario
→ Criterio de aceptación
→ Caso de prueba
→ Commit o Pull Request
→ Versión desplegada
```

---

## 23. Definición de terminado

Una funcionalidad se considerará terminada cuando:

- Su especificación esté aprobada.
- Existan reglas de negocio.
- Tenga criterios de aceptación.
- Incluya migraciones cuando sean necesarias.
- Incluya validación mediante Form Request.
- Incluya autorización mediante Policy.
- La lógica compleja esté fuera del controlador.
- Los cálculos críticos se realicen en servidor.
- La interfaz tenga estados de carga, error y vacío.
- Existan pruebas automatizadas.
- Haya pasado revisión de código.
- La documentación esté actualizada.
- Sea trazable hasta un objetivo.
- Pase integración continua.
- No tenga errores críticos abiertos.
- Haya sido validada en ambiente de pruebas.

---

## 24. Criterios de calidad del proyecto

- Código tipado y consistente.
- Controladores delgados.
- Consultas eficientes.
- Ausencia de N+1 en pantallas principales.
- Autorización probada.
- Reglas monetarias probadas.
- Migraciones reproducibles.
- Procesos críticos transaccionales.
- Historial inmutable donde corresponda.
- Estados y transiciones documentados.
- Auditoría de operaciones críticas.
- Documentación sincronizada con la implementación.

---

## 25. Riesgos y mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Requerimientos ambiguos del documento fuente | Alto | Taller de validación y glosario aprobado. |
| Datos históricos incompletos | Alto | Plantilla, validación, reporte de inconsistencias y carga piloto. |
| Duplicidad de clientes o unidades | Alto | Normalización, índices y revisión asistida. |
| Cambios frecuentes de flujo | Medio | Estados configurados en especificación y ADR. |
| Errores monetarios | Alto | Cálculo en servidor, decimal y pruebas exhaustivas. |
| Permisos mal configurados | Alto | Matriz, Policies y pruebas por rol. |
| Crecimiento de archivos | Medio | Límites, almacenamiento externo futuro y monitoreo. |
| Reportes lentos | Medio | Índices, agregaciones y Jobs para exportaciones. |
| Alcance excesivo | Alto | Separación estricta entre MVP y segunda etapa. |

---

## 26. Supuestos adoptados

- Laravel 12 y PHP 8.2 o superior.
- Jetstream con Inertia y Vue.
- TypeScript en frontend.
- MySQL como única base principal.
- Aplicación interna durante el MVP.
- Solicitudes de facturación sin timbrado CFDI.
- Sin multi-tenancy inicial.
- Una unidad pertenece a un solo cliente activo.
- La información histórica puede requerir limpieza manual.
- Inventario, alertas e integración CFDI son ampliaciones.
- Los detalles técnicos y criterios incluidos son especificaciones propuestas derivadas del resumen proporcionado.

---

## 27. Decisiones pendientes

Antes de cerrar la especificación deberán resolverse:

1. Paquete o estrategia para roles y permisos.
2. Formato exacto de folios.
3. Tasas e impuestos aplicables.
4. Reglas de redondeo.
5. Límite de descuentos por rol.
6. Tamaño y tipos permitidos de archivos.
7. Política de retención de evidencias.
8. Alcance de unicidad del número económico.
9. Procedimiento para cambio de propietario.
10. Formatos PDF oficiales.
11. Proveedor de almacenamiento en producción.
12. Infraestructura y frecuencia de respaldos.
13. Navegadores y dispositivos mínimos soportados.
14. Volumen estimado de clientes, unidades, órdenes y archivos.

---

## 28. Orden recomendado de implementación

1. Validación del alcance.
2. Especificaciones SDD.
3. Arquitectura y base de datos.
4. Inicialización técnica.
5. Autenticación y permisos.
6. Clientes.
7. Unidades.
8. Catálogos.
9. Órdenes de mantenimiento.
10. Historial.
11. Cotizaciones.
12. Solicitudes de facturación.
13. Dashboard y reportes.
14. Importación histórica.
15. Seguridad y respaldos.
16. Pruebas de aceptación.
17. Despliegue.
18. Documentación y cierre.

---

## 29. Validación final del documento

Antes de aprobar esta especificación se verificará:

- Todos los objetivos del documento fuente están cubiertos.
- No se atribuyen al PDF funciones no establecidas sin marcarlas como propuestas.
- Las ampliaciones futuras están separadas del MVP.
- Los hitos contienen objetivo, actividades, entregables y aceptación.
- El modelo de datos soporta los flujos.
- Los roles y permisos están definidos.
- Cada módulo tiene pruebas previstas.
- El roadmap respeta dependencias.
- La sintaxis Markdown es válida.
- El documento puede utilizarse como roadmap.
- Las decisiones pendientes tienen responsable y fecha límite.

---

## 30. Siguiente paso de ejecución

Realizar el **Hito 0 — Validación del alcance** y convertir este documento maestro en especificaciones separadas dentro de `specs/`, comenzando por:

1. `00-vision.md`.
2. `01-scope.md`.
3. `02-glossary.md`.
4. `03-actors-permissions.md`.
5. `04-functional-requirements.md`.
6. `05-business-rules.md`.
7. `traceability-matrix.md`.

Una vez aprobados estos archivos, podrá iniciarse el diseño definitivo de base de datos y arquitectura sin introducir decisiones funcionales no documentadas.
