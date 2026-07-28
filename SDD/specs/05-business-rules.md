# 05. Reglas de Negocio

> **Archivo:** `specs/05-business-rules.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Trazabilidad:** Plan de Desarrollo SDD — Sección 10 (Reglas de negocio generales) y Sección 11 (Especificaciones por módulo)

---

## 1. Propósito

Este documento detalla las reglas de negocio del Sistema de Mantenimiento Preventivo para Unidades, organizadas por categoría y con referencias cruzadas a los requerimientos funcionales que las aplican. Cada regla incluye identificador único, descripción, justificación, criterios de validación y trazabilidad.

---

## 2. Control del documento

| Campo | Valor |
|---|---|
| Producto | Sistema de mantenimiento preventivo para unidades |
| Versión | 1.0.0 |
| Fecha | Pendiente |
| Responsable | Pendiente |

### 2.1 Historial de versiones

| Versión | Fecha | Cambio | Responsable |
|---|---|---|---|
| 1.0.0 | Pendiente | Especificación inicial | Pendiente |

---

## 3. Clasificación de reglas

Las reglas de negocio se clasifican en las siguientes categorías:

- **RN-GEN**: Reglas generales aplicables a todo el sistema
- **RN-CLI**: Reglas específicas de clientes
- **RN-UNI**: Reglas específicas de unidades
- **RN-CAT**: Reglas de catálogos
- **RN-ORD**: Reglas de órdenes de mantenimiento
- **RN-COT**: Reglas de cotizaciones
- **RN-FAC**: Reglas de solicitudes de facturación
- **RN-ARC**: Reglas de archivos adjuntos
- **RN-AUD**: Reglas de auditoría

---

## 4. Reglas de negocio generales

### RN-GEN-001 — Generación de folios únicos

**Descripción:** Los folios deben ser únicos por tipo documental y generarse en servidor mediante una secuencia transaccional.

**Justificación:** Garantizar la integridad y trazabilidad de los documentos principales del sistema (órdenes, cotizaciones, solicitudes de facturación).

**Aplicación:**
- Órdenes de mantenimiento
- Cotizaciones
- Solicitudes de facturación

**Criterios de validación:**
- El folio se genera automáticamente al crear el documento.
- No existen dos documentos del mismo tipo con el mismo folio.
- La generación es atómica y protegida contra concurrencia.
- El folio sigue un formato legible: `{prefijo}-{correlativo}` (ej. `ORD-2025-00001`).

**Trazabilidad:** RF-ORD-001, RF-COT-001, RF-FAC-001, RF-CON-002

**Excepciones:** Ninguna. Los folios duplicados causarán error transaccional.

---

### RN-GEN-002 — Cálculo de importes en servidor

**Descripción:** Todos los importes se recalcularán en servidor. Los valores enviados por el navegador no serán fuente de verdad.

**Justificación:** Prevenir manipulación de precios, descuentos o totales desde el cliente. Garantizar consistencia financiera.

**Aplicación:**
- Órdenes de mantenimiento
- Cotizaciones
- Solicitudes de facturación

**Criterios de validación:**
- El backend recalcula subtotal, descuentos, impuestos y total.
- Los valores del frontend se usan solo como referencia inicial.
- Cualquier discrepancia genera error de validación.
- Los cálculos usan precisión decimal adecuada (mínimo 2 decimales para moneda).

**Trazabilidad:** RF-ORD-005, RF-COT-002, RF-FAC-001

**Excepciones:** Ninguna.

---

### RN-GEN-003 — Conservación de historial financiero

**Descripción:** Las cotizaciones, órdenes y solicitudes conservarán instantáneas de precio, impuestos, descuentos y datos fiscales utilizados al momento de su emisión.

**Justificación:** Garantizar que los documentos históricos reflejen las condiciones vigentes al momento de su creación, independientemente de cambios posteriores en catálogos o perfiles fiscales.

**Aplicación:**
- Precios de servicios y refacciones en órdenes y cotizaciones
- Datos fiscales en solicitudes de facturación
- Tasas de impuestos vigentes al momento de emisión

**Criterios de validación:**
- Los precios se almacenan en el documento, no solo como referencia al catálogo.
- Los datos fiscales se guardan como snapshot JSON o campos dedicados.
- Cambios posteriores en catálogos no afectan documentos existentes.
- El historial es consultable y auditable.

**Trazabilidad:** RF-CLI-004, RF-CAT-003, RF-CAT-004, RF-ORD-003, RF-ORD-004, RF-COT-006, RF-FAC-002

**Excepciones:** Ninguna.

---

### RN-GEN-004 — Auditoría de cambios críticos

**Descripción:** Los cambios críticos registrarán usuario, fecha, recurso, acción, valores anteriores y valores posteriores cuando sea razonable.

**Justificación:** Proveer trazabilidad completa de operaciones sensibles para fines de control, investigación y cumplimiento.

**Aplicación:**
- Creación, edición y eliminación lógica de registros principales
- Cambios de estado en órdenes, cotizaciones y solicitudes
- Modificaciones a datos fiscales
- Cambios de permisos y roles
- Reapertura de órdenes terminadas

**Criterios de validación:**
- El registro de auditoría es inmutable.
- Incluye: usuario, timestamp, entidad, ID, acción, before, after.
- Los administradores pueden consultar la auditoría.
- El rendimiento no se degrada significativamente por la auditoría.

**Trazabilidad:** RF-AUD-001, RF-AUD-002, RF-CLI-005, RF-UNI-005, RF-ORD-006, RF-CON-001

**Excepciones:** Consultas y lecturas no se auditan. Datos temporales o de sesión no se auditan.

---

### RN-GEN-005 — Eliminación lógica de registros

**Descripción:** Los registros con impacto histórico no se eliminarán físicamente. Se cancelarán, desactivarán o archivarán según el módulo.

**Justificación:** Preservar la integridad histórica y permitir recuperación ante errores. Cumplir requisitos de retención documental.

**Aplicación:**
- Clientes
- Unidades
- Órdenes de mantenimiento
- Cotizaciones
- Solicitudes de facturación
- Catálogos (servicios, conceptos, tipos)

**Criterios de validación:**
- Existe campo `deleted_at` o `status` para marcar eliminación lógica.
- Los listados excluyen registros eliminados por defecto.
- Los historiales mantienen referencias intactas.
- Solo administradores pueden restaurar registros eliminados.

**Trazabilidad:** RF-CLI-005, RF-UNI-002, RF-CAT-001, RF-CAT-002, RF-ORD-008, RF-ARC-003

**Excepciones:** Datos temporales, sesiones, logs de actividad antiguos pueden eliminarse físicamente según política de retención.

---

### RN-GEN-006 — Validación estricta de archivos

**Descripción:** Los archivos deberán validarse por tipo, tamaño y extensión. El sistema no confiará únicamente en el nombre del archivo.

**Justificación:** Prevenir subida de archivos maliciosos o corruptos. Proteger integridad del sistema y almacenamiento.

**Aplicación:**
- Evidencias en órdenes de mantenimiento
- Adjuntos en cotizaciones
- Comprobantes en solicitudes de facturación
- Fotografías de unidades

**Criterios de validación:**
- Se valida extensión del archivo.
- Se valida MIME type real (contenido, no solo header).
- Se valida tamaño máximo configurable.
- Se escanean archivos cuando sea técnicamente viable.
- Archivos inválidos generan error claro.

**Trazabilidad:** RF-ORD-007, RF-ARC-001

**Excepciones:** Ninguna.

---

### RN-GEN-007 — Secuencia temporal válida

**Descripción:** Las fechas de negocio deberán seguir una secuencia válida; por ejemplo, una entrega no puede preceder a la terminación.

**Justificación:** Garantizar coherencia temporal de los procesos de negocio.

**Aplicación:**
- Órdenes de mantenimiento (recepción → diagnóstico → proceso → terminación → entrega)
- Cotizaciones (emisión → envío → aceptación)
- Solicitudes de facturación (creación → envío → facturación)

**Criterios de validación:**
- `fecha_entrega >= fecha_terminacion >= fecha_inicio >= fecha_recepcion`
- `fecha_aceptacion >= fecha_emision` en cotizaciones
- El sistema previene fechas inconsistentes.
- Las correcciones requieren autorización y auditoría.

**Trazabilidad:** RF-ORD-006, RF-COT-003, RF-COT-004, RF-FAC-004

**Excepciones:** Correcciones autorizadas con justificación registrada en auditoría.

---

### RN-GEN-008 — Transiciones de estado explícitas

**Descripción:** Las transiciones de estado serán explícitas y validadas en backend.

**Justificación:** Prevenir estados inválidos o inconsistencias en el flujo de trabajo.

**Aplicación:**
- Órdenes de mantenimiento
- Cotizaciones
- Solicitudes de facturación
- Unidades
- Clientes

**Criterios de validación:**
- Cada entidad define estados válidos y transiciones permitidas.
- El backend valida cada transición antes de aplicarla.
- Transiciones inválidas generan error.
- Cada cambio registra auditoría.

**Trazabilidad:** RF-UNI-005, RF-ORD-006, RF-COT-003, RF-COT-004, RF-COT-006, RF-FAC-004

**Excepciones:** Ninguna.

---

### RN-GEN-009 — Control de concurrencia en operaciones críticas

**Descripción:** Las operaciones de aprobación, folios, cambios de estado críticos y generación de solicitudes usarán transacciones y controles de concurrencia cuando aplique.

**Justificación:** Prevenir condiciones de carrera, folios duplicados o inconsistencias en operaciones simultáneas.

**Aplicación:**
- Generación de folios
- Aprobación de cotizaciones
- Conversión de cotización a orden
- Cambios de estado críticos
- Creación de solicitudes de facturación

**Criterios de validación:**
- Se usan transacciones de base de datos.
- Se implementan locks o versionado optimista cuando corresponda.
- Operaciones fallidas hacen rollback completo.
- El usuario recibe mensaje claro en caso de conflicto.

**Trazabilidad:** RF-ORD-005, RF-ORD-006, RF-COT-005, RF-FAC-001, RF-CON-002

**Excepciones:** Lecturas y consultas no requieren control de concurrencia.

---

## 5. Reglas de negocio por módulo

### 5.1 RN-CLI — Clientes

#### RN-CLI-001 — Unicidad de email

**Descripción:** El email de un cliente debe ser único cuando se proporcione.

**Justificación:** Evitar duplicidad en comunicación y identificación de clientes.

**Criterios de validación:**
- El sistema verifica unicidad antes de crear o actualizar.
- La validación ignora mayúsculas/minúsculas.
- Emails nulos o vacíos no cuentan para unicidad.

**Trazabilidad:** RF-CLI-001

---

#### RN-CLI-002 — Cliente activo para nuevas operaciones

**Descripción:** Un cliente desactivado no podrá asociarse a nuevas unidades u órdenes de mantenimiento.

**Justificación:** Prevenir operaciones con clientes inactivos o dados de baja.

**Criterios de validación:**
- El sistema valida estatus del cliente antes de crear unidad u orden.
- Clientes desactivados no aparecen en selectores por defecto.
- El historial permanece consultable.

**Trazabilidad:** RF-CLI-005, RF-UNI-001, RF-ORD-001

---

#### RN-CLI-003 — Perfil fiscal predeterminado

**Descripción:** Cuando un cliente tenga múltiples perfiles fiscales, uno deberá marcarse como predeterminado.

**Justificación:** Facilitar creación de solicitudes de facturación sin requerir selección manual cada vez.

**Criterios de validación:**
- Exactamente un perfil está marcado como `is_default = true`.
- Al crear nuevo perfil, el anterior predeterminado se actualiza.
- Las solicitudes usan el perfil predeterminado por defecto.

**Trazabilidad:** RF-CLI-004, RF-FAC-002

---

### 5.2 RN-UNI — Unidades

#### RN-UNI-001 — Normalización de placas

**Descripción:** Las placas se normalizarán a mayúsculas, sin espacios ni guiones antes de validar unicidad.

**Justificación:** Prevenir duplicados por variaciones de formato (ABC-123 vs ABC123 vs abc123).

**Criterios de validación:**
- El sistema normaliza: `trim(strtoupper(str_replace(['-', ' '], '', $plates)))`.
- La unicidad se valida sobre el valor normalizado.
- El valor original se conserva para visualización.

**Trazabilidad:** RF-UNI-001, RF-UNI-004

---

#### RN-UNI-002 — Unicidad de VIN

**Descripción:** El VIN (Vehicle Identification Number), cuando se proporcione, debe ser único.

**Justificación:** El VIN es un identificador físico único del vehículo a nivel mundial.

**Criterios de validación:**
- El sistema verifica unicidad de VIN no nulo.
- VIN nulo o vacío no cuenta para unicidad.
- La validación ignora mayúsculas/minúsculas y espacios.

**Trazabilidad:** RF-UNI-001

---

#### RN-UNI-003 — Unidad con historial no eliminable

**Descripción:** Una unidad con órdenes de mantenimiento asociadas no podrá eliminarse física ni lógicamente.

**Justificación:** Preservar integridad del historial de servicios.

**Criterios de validación:**
- El sistema verifica existencia de órdenes antes de eliminar.
- Si existen órdenes, se sugiere desactivar en lugar de eliminar.
- La eliminación física requiere intervención administrativa especial.

**Trazabilidad:** RF-UNI-002

---

#### RN-UNI-004 — Actualización de kilometraje

**Descripción:** El kilometraje actual de una unidad debe ser mayor o igual al registrado anteriormente.

**Justificación:** El kilometraje es acumulativo; una reducción indica error de captura.

**Criterios de validación:**
- El sistema compara con el último kilometraje registrado.
- Reducciones generan advertencia o error según configuración.
- Excepciones requieren justificación y auditoría.

**Trazabilidad:** RF-UNI-002, RF-ORD-001

---

### 5.3 RN-CAT — Catálogos

#### RN-CAT-001 — Concepto en uso no eliminable

**Descripción:** Un servicio o concepto utilizado en órdenes o cotizaciones no podrá eliminarse físicamente.

**Justificación:** Preservar integridad histórica de documentos financieros.

**Criterios de validación:**
- El sistema verifica uso en partidas antes de eliminar.
- Si hay uso, solo permite desactivación.
- Conceptos desactivados no aparecen en selectores nuevos.

**Trazabilidad:** RF-CAT-003, RF-CAT-004

---

#### RN-CAT-002 — Precio base referencial

**Descripción:** El precio base de catálogos es referencial y puede modificarse en cada orden o cotización.

**Justificación:** Permitir flexibilidad comercial manteniendo estándares de precio.

**Criterios de validación:**
- El frontend sugiere precio base del catálogo.
- El usuario puede modificar el precio por partida.
- El precio modificado se guarda en la partida, no afecta catálogo.

**Trazabilidad:** RF-CAT-003, RF-CAT-004, RF-ORD-003, RF-ORD-004, RF-COT-002

---

### 5.4 RN-ORD — Órdenes de mantenimiento

#### RN-ORD-001 — Orden requiere cliente y unidad válidos

**Descripción:** Una orden de mantenimiento debe asociarse a un cliente activo y una unidad existente.

**Justificación:** Garantizar trazabilidad y validez operativa.

**Criterios de validación:**
- El sistema valida existencia de cliente y unidad.
- El cliente debe estar activo.
- La unidad debe pertenecer al cliente especificado.

**Trazabilidad:** RF-ORD-001

---

#### RN-ORD-002 — Orden terminada inmodificable

**Descripción:** Una orden en estado "Terminada" o "Entregada" no podrá modificarse excepto por administrador.

**Justificación:** Preservar integridad de trabajo completado y facturado.

**Criterios de validación:**
- El sistema bloquea edición de partidas y totales en órdenes terminadas.
- Solo administradores pueden reabrir con justificación.
- La reapertura registra auditoría detallada.

**Trazabilidad:** RF-ORD-006, RF-ORD-008

---

#### RN-ORD-003 — Diagnóstico antes de proceso

**Descripción:** Una orden debe tener diagnóstico registrado antes de pasar a estado "En proceso".

**Justificación:** Asegurar que el técnico ha evaluado la unidad antes de iniciar trabajos.

**Criterios de validación:**
- El sistema valida existencia de diagnóstico antes de permitir transición.
- Sin diagnóstico, la transición genera error.
- El diagnóstico puede editarse mientras no esté en proceso.

**Trazabilidad:** RF-ORD-002, RF-ORD-006

---

#### RN-ORD-004 — Total positivo o cero

**Descripción:** El total de una orden debe ser mayor o igual a cero.

**Justificación:** Prevenir valores negativos que carecen de sentido comercial.

**Criterios de validación:**
- El sistema valida `total >= 0` antes de guardar.
- Descuentos no pueden exceder el subtotal.
- Valores negativos generan error.

**Trazabilidad:** RF-ORD-005

---

### 5.5 RN-COT — Cotizaciones

#### RN-COT-001 — Cotización enviada inmodificable

**Descripción:** Una cotización en estado "Enviada" no podrá modificarse directamente.

**Justificación:** Preservar integridad de propuesta formal enviada al cliente.

**Criterios de validación:**
- El sistema bloquea edición directa de cotizaciones enviadas.
- Para modificar, se debe generar nueva versión.
- Solo borradores son editables.

**Trazabilidad:** RF-COT-003, RF-COT-006

---

#### RN-COT-002 — Solo versión más reciente aceptable

**Descripción:** Solo la versión más reciente de una cotización puede aceptarse o convertirse en orden.

**Justificación:** Asegurar que se trabaja con la propuesta vigente.

**Criterios de validación:**
- El sistema identifica la versión con número más alto.
- Versiones anteriores no permiten aceptación o conversión.
- El historial de versiones permanece consultable.

**Trazabilidad:** RF-COT-005, RF-COT-006

---

#### RN-COT-003 — Vigencia de cotización

**Descripción:** Las cotizaciones tendrán una fecha de vigencia. Pasada la vigencia, la cotización no podrá aceptarse sin renovación.

**Justificación:** Reflejar validez temporal de precios y condiciones comerciales.

**Criterios de validación:**
- La cotización incluye `valid_until`.
- El sistema valida `fecha_actual <= valid_until` para aceptación.
- Cotizaciones vencidas requieren nueva versión o extensión de vigencia.

**Trazabilidad:** RF-COT-001, RF-COT-004

---

### 5.6 RN-FAC — Solicitudes de facturación

#### RN-FAC-001 — Solicitud basada en orden o cotización

**Descripción:** Una solicitud de facturación debe originarse desde una orden de mantenimiento o cotización válida.

**Justificación:** Garantizar que la facturación corresponde a servicios documentados.

**Criterios de validación:**
- La solicitud referencia `maintenance_order_id` o `quotation_id`.
- La entidad origen debe existir y estar en estado válido.
- No se permiten solicitudes sin origen documentado.

**Trazabilidad:** RF-FAC-001

---

#### RN-FAC-002 — Datos fiscales completos

**Descripción:** Una solicitud de facturación debe incluir todos los datos fiscales requeridos antes de enviarse a facturación.

**Justificación:** Cumplir requisitos fiscales para emisión de CFDI.

**Criterios de validación:**
- Campos obligatorios: razón social, RFC, régimen fiscal, uso de CFDI, código postal.
- El sistema valida presencia y formato de datos.
- Solicitud incompleta no puede cambiar a "Enviada a facturación".

**Trazabilidad:** RF-FAC-002, RF-FAC-004

---

#### RN-FAC-003 — Solicitud facturada inmodificable

**Descripción:** Una solicitud en estado "Facturada" no podrá modificarse.

**Justificación:** Preservar integridad de información ya facturada.

**Criterios de validación:**
- El sistema bloquea edición de solicitudes facturadas.
- Errores requieren nota de crédito o cancelación según proceda.
- Solo solicitudes en borrador son editables.

**Trazabilidad:** RF-FAC-004

---

### 5.7 RN-ARC — Archivos adjuntos

#### RN-ARC-001 — Límite de tamaño por archivo

**Descripción:** Los archivos adjuntos no podrán exceder el tamaño máximo configurado.

**Justificación:** Controlar uso de almacenamiento y prevenir abusos.

**Criterios de validación:**
- El sistema valida tamaño antes de subir.
- Tamaño máximo configurable (default: 10MB para imágenes, 20MB para PDF).
- Archivos excedidos generan error claro.

**Trazabilidad:** RF-ARC-001

---

#### RN-ARC-002 — Asociación polimórfica válida

**Descripción:** Los archivos adjuntos deben asociarse a una entidad válida existente.

**Justificación:** Prevenir archivos huérfanos sin contexto.

**Criterios de validación:**
- El archivo referencia `attachable_type` y `attachable_id`.
- La entidad debe existir en el momento de asociación.
- Archivos de entidades eliminadas se marcan para revisión.

**Trazabilidad:** RF-ARC-001, RF-ARC-003

---

### 5.8 RN-AUD — Auditoría

#### RN-AUD-001 — Inmutabilidad de registros de auditoría

**Descripción:** Los registros de auditoría no podrán modificarse ni eliminarse una vez creados.

**Justificación:** Garantizar integridad del historial de actividades.

**Criterios de validación:**
- La tabla de auditoría no permite UPDATE ni DELETE.
- Solo INSERT está permitido.
- Acceso de lectura restringido a roles autorizados.

**Trazabilidad:** RF-AUD-001, RN-GEN-004

---

## 6. Matriz de validación

| Regla | Requerimientos relacionados | Módulo(s) | Tipo de validación |
|---|---|---|---|
| RN-GEN-001 | RF-ORD-001, RF-COT-001, RF-FAC-001 | Todos | Transaccional |
| RN-GEN-002 | RF-ORD-005, RF-COT-002 | ORD, COT, FAC | Backend recalc |
| RN-GEN-003 | RF-CLI-004, RF-CAT-003 | Todos | Snapshot |
| RN-GEN-004 | RF-AUD-001 | Todos | Audit log |
| RN-GEN-005 | RF-CLI-005, RF-UNI-002 | Todos | Soft delete |
| RN-GEN-006 | RF-ORD-007, RF-ARC-001 | ARC | Validación archivo |
| RN-GEN-007 | RF-ORD-006 | ORD, COT, FAC | Validación fechas |
| RN-GEN-008 | RF-UNI-005, RF-ORD-006 | Todos | Máquina de estados |
| RN-GEN-009 | RF-ORD-005, RF-COT-005 | ORD, COT, FAC | Transaccional |

---

## 7. Aprobaciones

| Rol | Nombre | Firma | Fecha |
|---|---|---|---|
| Responsable operativo | Pendiente | Pendiente | Pendiente |
| Responsable administrativo | Pendiente | Pendiente | Pendiente |
| Responsable técnico | Pendiente | Pendiente | Pendiente |
| Patrocinador | Pendiente | Pendiente | Pendiente |

---

**Fin del documento**
