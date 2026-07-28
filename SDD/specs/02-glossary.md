# Glosario del Dominio — Sistema de Mantenimiento Preventivo para Unidades

> **Archivo:** `02-glossary.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Metodología:** Specification-Driven Development (SDD)  
> **Documento maestro:** `PLAN_DESARROLLO_SDD_MANTENIMIENTO_UNIDADES.md`

---

## 1. Términos del Dominio

| Término | Definición | Notas |
|---|---|---|
| Cliente | Persona física o moral propietaria o responsable de una o más unidades. | Puede tener múltiples unidades asociadas. |
| Unidad | Vehículo registrado en el sistema y asociado a un cliente activo. | También referido como "vehículo". |
| Número económico | Identificador interno asignado a una unidad por el cliente o la empresa. | No necesariamente único entre clientes. |
| VIN | Vehicle Identification Number. Número de identificación vehicular de 17 caracteres. | Único a nivel global por vehículo. |
| Placas | Identificador alfanumérico visible en la unidad. | Puede cambiar durante la vida útil del vehículo. |
| Servicio | Trabajo normalizado que puede incluirse en una orden o cotización. | Ejemplo: cambio de aceite, revisión de frenos. |
| Mantenimiento preventivo | Intervención planificada para reducir la probabilidad de falla. | Se programa por tiempo o kilometraje. |
| Mantenimiento correctivo | Intervención realizada para corregir una falla detectada. | Responde a una avería existente. |
| Orden de mantenimiento | Registro operativo que controla una intervención desde la recepción hasta la entrega. | Documento principal del flujo operativo. |
| Refacción | Material, pieza o componente utilizado durante un servicio. | También llamado "repuesto" o "parte". |
| Cotización | Propuesta económica emitida para servicios, mano de obra o refacciones. | Puede convertirse en orden de mantenimiento. |
| Solicitud de facturación | Expediente administrativo que concentra la información necesaria para emitir una factura. | No es un comprobante fiscal en sí mismo. |
| Evidencia | Fotografía, documento o archivo relacionado con un cliente, unidad, orden, cotización o solicitud. | Se almacena como adjunto. |
| Folio | Identificador legible y único generado por el sistema. | Formato: prefijo + secuencia numérica. |
| Estado | Etapa vigente dentro del flujo de un registro. | Determina acciones disponibles. |
| Perfil fiscal | Conjunto de datos fiscales de un cliente para facturación. | Incluye RFC, régimen fiscal, uso de CFDI. |
| Concepto | Elemento facturable dentro de una cotización u orden. | Puede ser servicio o refacción. |
| Mano de obra | Costo asociado al trabajo técnico realizado. | Se registra por hora o por servicio. |
| Subtotal | Suma de conceptos antes de impuestos y descuentos. | Base para cálculo de impuestos. |
| Descuento | Reducción aplicada al subtotal. | Puede ser porcentual o fija. |
| Impuesto | Carga fiscal aplicable según legislación. | IVA, IEPS, etc. |
| Total | Monto final después de sumar impuestos y restar descuentos. | Cantidad a pagar. |
| Trazabilidad | Capacidad de rastrear el historial completo de cambios y operaciones. | Esencial para auditoría. |
| Auditoría | Registro de quién, cuándo y qué cambió en el sistema. | Logs de actividad. |
| Rol | Conjunto de permisos asignados a un usuario. | Ejemplo: Administrador, Técnico. |
| Permiso | Capacidad específica para realizar una acción. | Ejemplo: crear_cliente, editar_orden. |
| Policy | Clase que define reglas de autorización en Laravel. | Controla acceso a nivel de modelo. |
| Gate | Mecanismo de autorización global en Laravel. | Para capacidades transversales. |

---

## 2. Acrónimos

| Acrónimo | Significado |
|---|---|
| VIN | Vehicle Identification Number |
| CFDI | Comprobante Fiscal Digital por Internet |
| PAC | Proveedor Autorizado de Certificación |
| RFC | Registro Federal de Contribuyentes |
| IVA | Impuesto al Valor Agregado |
| IEPS | Impuesto Especial sobre Producción y Servicios |
| MVP | Minimum Viable Product (Producto Mínimo Viable) |
| SDD | Specification-Driven Development |
| ADR | Architecture Decision Record |
| API | Application Programming Interface |
| DTO | Data Transfer Object |
| E2E | End-to-End (pruebas de extremo a extremo) |
| IoT | Internet of Things |
| GPS | Global Positioning System |
| UX | User Experience |
| UI | User Interface |

---

## 3. Constantes y Valores del Sistema

| Constante | Valor | Descripción |
|---|---|---|
| ZONA_HORARIA | `America/Mexico_City` | Zona horaria predeterminada. |
| MONEDA | `MXN` | Moneda predeterminada. |
| IDIOMA | `es` | Idioma de la interfaz. |
| PRECISION_MONETARIA | `2` | Decimales para montos. |
| LONGITUD_VIN | `17` | Caracteres esperados en VIN. |

---

## 4. Estados del Sistema

### 4.1 Estados de Cliente

| Estado | Código | Descripción |
|---|---|---|
| Activo | `active` | Cliente habilitado para operar. |
| Inactivo | `inactive` | Cliente temporalmente deshabilitado. |
| Eliminado | `deleted` | Cliente eliminado lógicamente. |

### 4.2 Estados de Unidad

| Estado | Código | Descripción |
|---|---|---|
| Activa | `active` | Unidad en operación. |
| En mantenimiento | `in_maintenance` | Unidad recibida para servicio. |
| Inactiva | `inactive` | Unidad temporalmente fuera de servicio. |
| Eliminada | `deleted` | Unidad eliminada lógicamente. |

### 4.3 Estados de Orden de Mantenimiento

| Estado | Código | Descripción |
|---|---|---|
| Recibida | `received` | Unidad ingresada, pendiente de diagnóstico. |
| En diagnóstico | `diagnosing` | Técnico realizando evaluación. |
| En espera de autorización | `pending_approval` | Requiere aprobación del cliente. |
| Autorizada | `approved` | Lista para iniciar trabajo. |
| En proceso | `in_progress` | Trabajo en ejecución. |
| Completada | `completed` | Trabajo terminado, pendiente entrega. |
| Entregada | `delivered` | Unidad entregada al cliente. |
| Cancelada | `cancelled` | Orden cancelada. |

### 4.4 Estados de Cotización

| Estado | Código | Descripción |
|---|---|---|
| Borrador | `draft` | En elaboración. |
| Enviada | `sent` | Enviada al cliente. |
| Aceptada | `accepted` | Cliente aprobó la cotización. |
| Rechazada | `rejected` | Cliente rechazó la cotización. |
| Expirada | `expired` | Fuera de vigencia. |
| Convertida | `converted` | Se generó orden a partir de esta cotización. |
| Cancelada | `cancelled` | Cotización cancelada. |

### 4.5 Estados de Solicitud de Facturación

| Estado | Código | Descripción |
|---|---|---|
| Pendiente | `pending` | Información incompleta o en revisión. |
| Lista | `ready` | Lista para procesamiento fiscal. |
| Procesada | `processed` | Factura emitida externamente. |
| Cancelada | `cancelled` | Solicitud cancelada. |

---

## 5. Tipos de Documentos

| Tipo | Prefijo | Descripción |
|---|---|---|
| Orden de mantenimiento | `OM` | Folio para órdenes de servicio. |
| Cotización | `COT` | Folio para cotizaciones. |
| Solicitud de facturación | `SF` | Folio para solicitudes de facturación. |

---

## 6. Trazabilidad con Documento Maestro

| Sección | Referencia en Plan Maestro |
|---|---|
| Términos del dominio | Sección 6 |
| Estados | Secciones 11.x (por módulo) |

---

## 7. Control de Cambios

| Versión | Fecha | Cambio | Responsable |
|---|---|---|---|
| 1.0.0 | Pendiente | Especificación inicial | Pendiente |

---

## 8. Aprobaciones Requeridas

- [ ] Responsable operativo.
- [ ] Responsable administrativo.
- [ ] Responsable técnico.
- [ ] Patrocinador o propietario del producto.
