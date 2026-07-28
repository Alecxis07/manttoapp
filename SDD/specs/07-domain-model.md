# 07 - Modelo de Dominio

**Código del Documento:** SDD-MOD-DOM-001  
**Versión:** 1.0  
**Fecha:** 2026-01-15  
**Estado:** En desarrollo  

---

## Control de Cambios

| Versión | Fecha       | Autor           | Descripción del Cambio                           | Aprobado Por |
|---------|-------------|-----------------|--------------------------------------------------|--------------|
| 1.0     | 2026-01-15  | Sistema SDD     | Creación inicial del modelo de dominio           | Pendiente    |

---

## 1. Introducción

### 1.1 Propósito
Este documento define el modelo de dominio del sistema SSD (Sistema de Servicios de Distribución), describiendo las entidades principales, sus atributos, relaciones y reglas de integridad.

### 1.2 Alcance
El modelo cubre todos los módulos del sistema: seguridad, clientes, unidades, categorías, órdenes, historial, cotizaciones, facturación, reportes, auditoría, archivos y configuración.

### 1.3 Referencias
- [00-vision.md](00-vision.md) - Visión del producto
- [02-glossary.md](02-glossary.md) - Glosario de términos
- [04-functional-requirements.md](04-functional-requirements.md) - Requerimientos funcionales

---

## 2. Entidades Core del Sistema

### 2.1 Usuario
**Descripción:** Representa a un usuario del sistema con credenciales de acceso.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| nombre         | String    | Sí          | Nombre completo del usuario          |
| email          | String    | Sí          | Correo electrónico (único)           |
| password       | Hash      | Sí          | Contraseña encriptada                |
| estado         | Enum      | Sí          | ACTIVO, INACTIVO, SUSPENDIDO         |
| ultimoAcceso   | DateTime  | No          | Fecha y hora del último acceso       |
| intentosFallidos| Integer  | No          | Intentos de login fallidos           |
| bloqueoHasta   | DateTime  | No          | Fecha de desbloqueo si está bloqueado|
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Relaciones:**
- Tiene muchos Roles (muchos-a-muchos)
- Tiene un Perfil (uno-a-uno)
- Genera muchas Auditorías (uno-a-muchos)

**Reglas de Integridad:**
- El email debe ser único en todo el sistema
- La contraseña debe cumplir con la política de seguridad (RN-SEG-001)
- Un usuario inactivo no puede autenticarse

---

### 2.2 Rol
**Descripción:** Define un conjunto de permisos que pueden asignarse a usuarios.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| nombre         | String    | Sí          | Nombre del rol                       |
| codigo         | String    | Sí          | Código único del rol                 |
| descripcion    | String    | No          | Descripción detallada                |
| esSistema      | Boolean   | Sí          | Indica si es rol del sistema         |
| permisos       | UUID[]    | Sí          | Lista de IDs de permisos             |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Relaciones:**
- Pertenece a muchos Usuarios (muchos-a-muchos)
- Contiene muchos Permisos (muchos-a-muchos)

**Reglas de Integridad:**
- Los roles de sistema no pueden eliminarse
- Todo usuario debe tener al menos un rol

---

### 2.3 Permiso
**Descripción:** Define una acción específica que puede realizarse sobre un recurso.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| codigo         | String    | Sí          | Código único (ej: ORD.CREAR)         |
| descripcion    | String    | Sí          | Descripción legible                  |
| modulo         | String    | Sí          | Módulo al que pertenece              |
| recurso        | String    | Sí          | Recurso sobre el que actúa           |
| accion         | String    | Sí          | Acción permitida (CREAR, LEER, etc.) |
| nivel          | Integer   | Sí          | Nivel de jerarquía                   |

**Valores permitidos para accion:**
- CREAR, LEER, ACTUALIZAR, ELIMINAR, EXPORTAR, IMPRIMIR, APROBAR, RECHAZAR, ANULAR

---

### 2.4 Perfil
**Descripción:** Información adicional y preferencias del usuario.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| usuarioId      | UUID      | Sí          | ID del usuario asociado              |
| bio            | Text      | No          | Biografía o descripción personal     |
| avatar         | String    | No          | URL de la imagen de perfil           |
| telefono       | String    | No          | Teléfono de contacto                 |
| direccion      | Object    | No          | Dirección completa                   |
| zonaHoraria    | String    | No          | Zona horaria preferida               |
| idioma         | String    | No          | Idioma preferido                     |
| notificaciones | JSON      | No          | Preferencias de notificación         |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 3. Entidades de Clientes

### 3.1 Cliente
**Descripción:** Representa a un cliente natural o jurídico del sistema.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| tipo           | Enum      | Sí          | NATURAL, JURIDICO                    |
| nombre         | String    | Sí          | Nombre o razón social                |
| documentoTipo  | String    | Sí          | DNI, RUC, CE, PASAPORTE              |
| documentoNumero| String    | Sí          | Número de documento (único por tipo) |
| email          | String    | No          | Correo electrónico                   |
| telefono       | String    | No          | Teléfono principal                   |
| telefonoAlterno| String    | No          | Teléfono secundario                  |
| direccion      | Object    | No          | Dirección completa                   |
| estado         | Enum      | Sí          | ACTIVO, INACTIVO                     |
| fechaRegistro  | DateTime  | Sí          | Fecha de registro                    |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Estructura de dirección:**
```json
{
  "tipoVia": "Calle",
  "nombreVia": "Los Pinos",
  "numero": "123",
  "interior": "4B",
  "urbanizacion": "San Isidro",
  "distrito": "San Isidro",
  "provincia": "Lima",
  "departamento": "Lima",
  "codigoPostal": "15073",
  "referencia": "Cruce con Av. Javier Prado",
  "coordenadas": {
    "latitud": -12.0978,
    "longitud": -77.0365
  }
}
```

**Relaciones:**
- Tiene muchos Contactos (uno-a-muchos)
- Genera muchas Órdenes (uno-a-muchos)
- Genera muchas Cotizaciones (uno-a-muchos)
- Genera muchas Facturas (uno-a-muchos)

---

### 3.2 Contacto
**Descripción:** Persona de contacto asociada a un cliente jurídico.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| clienteId      | UUID      | Sí          | ID del cliente asociado              |
| nombre         | String    | Sí          | Nombre completo                      |
| cargo          | String    | No          | Cargo o posición                     |
| departamento   | String    | No          | Departamento                         |
| email          | String    | No          | Correo electrónico                   |
| telefono       | String    | No          | Teléfono directo                     |
| celular        | String    | No          | Número celular                       |
| esPrincipal    | Boolean   | Sí          | Indica si es contacto principal      |
| recibeNotif    | Boolean   | Sí          | Recibe notificaciones                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 4. Entidades de Unidades

### 4.1 Unidad
**Descripción:** Representa un vehículo o equipo sujeto a mantenimiento.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| codigo         | String    | Sí          | Código interno (único)               |
| nombre         | String    | Sí          | Nombre descriptivo                   |
| tipo           | Enum      | Sí          | VEHICULO, MAQUINARIA, EQUIPO         |
| marca          | String    | Sí          | Marca del fabricante                 |
| modelo         | String    | Sí          | Modelo específico                    |
| version        | String    | No          | Versión o variante                   |
| placa          | String    | No          | Placa de rodaje                      |
| VIN            | String    | No          | Número de chasis                     |
| motor          | String    | No          | Número de motor                      |
| color          | String    | No          | Color principal                      |
| anio           | Integer   | Sí          | Año de fabricación                   |
| kilometraje    | Integer   | Sí          | Kilometraje actual                   |
| horometro      | Integer   | No          | Horas de operación                   |
| combustible    | Enum      | No          | GASOLINA, DIESEL, ELECTRICO, HYBRID  |
| transmision    | Enum      | No          | MANUAL, AUTOMATICA                   |
| estado         | Enum      | Sí          | ACTIVO, MANTENIMIENTO, BAJA          |
| ubicacion      | Object    | No          | Ubicación actual                     |
| clienteId      | UUID      | No          | ID del cliente propietario           |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Relaciones:**
- Pertenece a un Cliente (muchos-a-uno)
- Tiene muchos Mantenimientos (uno-a-muchos)
- Tiene muchos Seguros (uno-a-muchos)
- Tiene muchos Documentos (uno-a-muchos)
- Genera muchas Órdenes (uno-a-muchos)

---

### 4.2 Mantenimiento
**Descripción:** Registro de mantenimiento preventivo o correctivo de una unidad.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| unidadId       | UUID      | Sí          | ID de la unidad                      |
| tipo           | Enum      | Sí          | PREVENTIVO, CORRECTIVO, PREDICTIVO   |
| categoria      | String    | Sí          | Categoría del mantenimiento          |
| fecha          | DateTime  | Sí          | Fecha de ejecución                   |
| kilometraje    | Integer   | No          | Kilometraje al momento               |
| horometro      | Integer   | No          | Horómetro al momento                 |
| costo          | Decimal   | No          | Costo total                          |
| descripcion    | Text      | Sí          | Descripción detallada                |
| actividades    | Array     | Sí          | Lista de actividades realizadas      |
| repuestos      | Array     | No          | Lista de repuestos usados            |
| manoObra       | Decimal   | No          | Horas de mano de obra                |
| tecnico        | String    | No          | Técnico responsable                  |
| proximoMant    | DateTime  | No          | Fecha próximo mantenimiento          |
| garantia       | Integer   | No          | Días de garantía                     |
| ordenId        | UUID      | No          | ID de orden asociada                 |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |

---

### 4.3 Seguro
**Descripción:** Póliza de seguro asociada a una unidad.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| unidadId       | UUID      | Sí          | ID de la unidad                      |
| compania       | String    | Sí          | Compañía aseguradora                 |
| poliza         | String    | Sí          | Número de póliza                     |
| tipo           | Enum      | Sí          | RESPONSABILIDAD, TODO_RIESGO, etc.   |
| vigenciaDesde  | DateTime  | Sí          | Inicio de vigencia                   |
| vigenciaHasta  | DateTime  | Sí          | Fin de vigencia                      |
| cobertura      | Text      | No          | Descripción de coberturas            |
| deducible      | Decimal   | No          | Monto deducible                      |
| prima          | Decimal   | Sí          | Monto de la prima                    |
| estado         | Enum      | Sí          | ACTIVO, VENCIDO, CANCELADO           |
| archivoPoliza  | UUID      | No          | ID del archivo de la póliza          |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 5. Entidades de Categorías

### 5.1 Categoria
**Descripción:** Clasificación jerárquica para servicios, productos o recursos.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| codigo         | String    | Sí          | Código único                         |
| nombre         | String    | Sí          | Nombre de la categoría               |
| descripcion    | String    | No          | Descripción detallada                |
| padreId        | UUID      | No          | ID de la categoría padre             |
| nivel          | Integer   | Sí          | Nivel en la jerarquía (0 = raíz)     |
| ruta           | String    | Sí          | Ruta completa (ej: 1.5.12)           |
| orden          | Integer   | Sí          | Orden de visualización               |
| activa         | Boolean   | Sí          | Indica si está activa                |
| icono          | String    | No          | Icono representativo                 |
| color          | String    | No          | Color asociado                       |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Reglas de Integridad:**
- Una categoría no puede ser su propio padre
- El nivel debe coincidir con la profundidad en la jerarquía
- No se puede eliminar una categoría con hijos

---

## 6. Entidades de Órdenes de Servicio

### 6.1 Orden
**Descripción:** Solicitud de servicio o trabajo a realizar.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| numero         | String    | Sí          | Número correlativo (único)           |
| clienteId      | UUID      | Sí          | ID del cliente                       |
| unidadId       | UUID      | No          | ID de la unidad                      |
| categoriaId    | UUID      | Sí          | ID de la categoría                   |
| tipo           | Enum      | Sí          | SERVICIO, REPUESTO, INSPECCION       |
| prioridad      | Enum      | Sí          | BAJA, MEDIA, ALTA, CRITICA           |
| estado         | Enum      | Sí          | BORRADOR, PENDIENTE, EN_PROGRESO, COMPLETADA, CANCELADA |
| titulo         | String    | Sí          | Título descriptivo                   |
| descripcion    | Text      | Sí          | Descripción detallada                |
| solicitud      | Text      | No          | Motivo de la solicitud               |
| diagnostico    | Text      | No          | Diagnóstico técnico                  |
| solucion       | Text      | No          | Solución aplicada                    |
| fechaSolicitada| DateTime  | Sí          | Fecha de solicitud                   |
| fechaProgramada| DateTime  | No          | Fecha programada                     |
| fechaInicio    | DateTime  | No          | Fecha de inicio real                 |
| fechaFin       | DateTime  | No          | Fecha de finalización                |
| fechaEntrega   | DateTime  | No          | Fecha de entrega al cliente          |
| costoEstimado  | Decimal   | No          | Costo estimado                       |
| costoReal      | Decimal   | No          | Costo real final                     |
| asignadoA      | UUID      | No          | ID del técnico asignado              |
| ubicacion      | Object    | No          | Ubicación del servicio               |
| checklist      | Array     | No          | Lista de verificación                |
| fotos          | UUID[]    | No          | IDs de fotos adjuntas                |
| archivos       | UUID[]    | No          | IDs de archivos adjuntos             |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

**Relaciones:**
- Pertenece a un Cliente (muchos-a-uno)
- Pertenece a una Unidad (muchos-a-uno)
- Pertenece a una Categoría (muchos-a-uno)
- Tiene muchos Items (uno-a-muchos)
- Tiene una Bitácora (uno-a-uno)
- Genera una Cotización (uno-a-uno)
- Genera una Factura (uno-a-uno)

---

### 6.2 ItemOrden
**Descripción:** Ítem individual dentro de una orden de servicio.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| ordenId        | UUID      | Sí          | ID de la orden padre                 |
| secuencia      | Integer   | Sí          | Número de secuencia                  |
| tipo           | Enum      | Sí          | MANO_OBRA, REPUESTO, SERVICIO, OTRO  |
| concepto       | String    | Sí          | Concepto o nombre                    |
| descripcion    | Text      | No          | Descripción detallada                |
| cantidad       | Decimal   | Sí          | Cantidad                             |
| unidadMedida   | String    | Sí          | Unidad de medida                     |
| precioUnitario | Decimal   | Sí          | Precio unitario                      |
| descuento      | Decimal   | No          | Descuento aplicado                   |
| subtotal       | Decimal   | Sí          | Subtotal (calculado)                 |
| igv            | Decimal   | Sí          | IGV (calculado)                      |
| total          | Decimal   | Sí          | Total (calculado)                    |
| estado         | Enum      | Sí          | PENDIENTE, EN_PROGRESO, COMPLETADO   |
| completado     | Boolean   | Sí          | Indica si está completado            |
| observado      | Boolean   | No          | Indica si tiene observaciones        |
| observacion    | Text      | No          | Observación si existe                |
| referencia     | String    | No          | Referencia externa                   |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

### 6.3 Bitacora
**Descripción:** Registro cronológico de actividades de una orden.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| ordenId        | UUID      | Sí          | ID de la orden                       |
| fechaHora      | DateTime  | Sí          | Fecha y hora del registro            |
| usuarioId      | UUID      | Sí          | ID del usuario que registra          |
| actividad      | String    | Sí          | Tipo de actividad                    |
| descripcion    | Text      | Sí          | Descripción detallada                |
| tiempoInvertido| Integer   | No          | Minutos invertidos                   |
| fotos          | UUID[]    | No          | IDs de fotos adjuntas                |
| archivos       | UUID[]    | No          | IDs de archivos adjuntos             |
| ubicacion      | Object    | No          | Ubicación donde se realizó           |
| firma          | String    | No          | Firma digital                        |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |

---

## 7. Entidades de Historial

### 7.1 HistorialServicio
**Descripción:** Registro consolidado del historial de servicios de una unidad.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| unidadId       | UUID      | Sí          | ID de la unidad                      |
| clienteId      | UUID      | Sí          | ID del cliente                       |
| fechaServicio  | DateTime  | Sí          | Fecha del servicio                   |
| tipoServicio   | String    | Sí          | Tipo de servicio realizado           |
| descripcion    | Text      | Sí          | Descripción del servicio             |
| tecnicos       | Array     | No          | Lista de técnicos participantes      |
| repuestos      | Array     | No          | Lista de repuestos utilizados        |
| horasManoObra  | Decimal   | No          | Horas de mano de obra                |
| costoTotal     | Decimal   | Sí          | Costo total del servicio             |
| garantiaDias   | Integer   | No          | Días de garantía otorgados           |
| proximoServicio| DateTime  | No          | Fecha recomendada próximo servicio   |
| rating         | Float     | No          | Calificación del cliente (1-5)       |
| comentarios    | Text      | No          | Comentarios del cliente              |
| archivos       | UUID[]    | No          | IDs de archivos adjuntos             |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |

---

## 8. Entidades de Cotizaciones

### 8.1 Cotizacion
**Descripción:** Propuesta comercial con precios y condiciones.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| numero         | String    | Sí          | Número correlativo (único)           |
| clienteId      | UUID      | Sí          | ID del cliente                       |
| unidadId       | UUID      | No          | ID de la unidad                      |
| ordenId        | UUID      | No          | ID de la orden asociada              |
| estado         | Enum      | Sí          | BORRADOR, ENVIADA, APROBADA, RECHAZADA, EXPIRADA, CONVERTIDA |
| validezDias    | Integer   | Sí          | Días de validez                      |
| fechaEmision   | DateTime  | Sí          | Fecha de emisión                     |
| fechaVencim    | DateTime  | Sí          | Fecha de vencimiento                 |
| subTotal       | Decimal   | Sí          | Subtotal                             |
| descuento      | Decimal   | No          | Descuento global                     |
| igv            | Decimal   | Sí          | IGV                                  |
| total          | Decimal   | Sí          | Total general                        |
| condiciones    | Array     | No          | Términos y condiciones               |
| notas          | Text      | No          | Notas adicionales                    |
| creadoPor      | UUID      | Sí          | ID del usuario creador               |
| revisadoPor    | UUID      | No          | ID del usuario revisor               |
| aprobadoPor    | UUID      | No          | ID del usuario aprobador             |
| convertidoEnFac| UUID      | No          | ID de factura convertida             |
| archivos       | UUID[]    | No          | IDs de archivos adjuntos             |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

### 8.2 ItemCotizacion
**Descripción:** Ítem individual dentro de una cotización.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| cotizacionId   | UUID      | Sí          | ID de la cotización padre            |
| secuencia      | Integer   | Sí          | Número de secuencia                  |
| tipo           | Enum      | Sí          | MANO_OBRA, REPUESTO, SERVICIO, OTRO  |
| concepto       | String    | Sí          | Concepto o nombre                    |
| descripcion    | Text      | No          | Descripción detallada                |
| cantidad       | Decimal   | Sí          | Cantidad                             |
| unidadMedida   | String    | Sí          | Unidad de medida                     |
| precioUnitario | Decimal   | Sí          | Precio unitario                      |
| descuento      | Decimal   | No          | Descuento aplicado                   |
| subtotal       | Decimal   | Sí          | Subtotal (calculado)                 |
| igv            | Decimal   | Sí          | IGV (calculado)                      |
| total          | Decimal   | Sí          | Total (calculado)                    |
| referencia     | String    | No          | Referencia externa                   |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 9. Entidades de Facturación

### 9.1 Factura
**Descripción:** Documento tributario electrónico.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| tipo           | Enum      | Sí          | FACTURA, BOLETA, NOTA_CREDITO, NOTA_DEBITO, TICKET |
| serie          | String    | Sí          | Serie del documento                  |
| numero         | String    | Sí          | Número correlativo                   |
| clienteId      | UUID      | Sí          | ID del cliente                       |
| ordenId        | UUID      | No          | ID de la orden asociada              |
| cotizacionId   | UUID      | No          | ID de la cotización origen           |
| estado         | Enum      | Sí          | BORRADOR, EMITIDA, ANULADA, ENVIADA, ACEPTADA, RECHAZADA |
| fechaEmision   | DateTime  | Sí          | Fecha de emisión                     |
| fechaVencim    | DateTime  | No          | Fecha de vencimiento                 |
| fechaEnvio     | DateTime  | No          | Fecha de envío a SUNAT               |
| subTotal       | Decimal   | Sí          | Subtotal                             |
| descuento      | Decimal   | No          | Descuento global                     |
| igv            | Decimal   | Sí          | IGV                                  |
| total          | Decimal   | Sí          | Total general                        |
| saldoPendiente | Decimal   | Sí          | Saldo pendiente de pago              |
| metodoPago     | Enum      | No          | CONTADO, CREDITO, TRANSFERENCIA, etc.|
| pagos          | Array     | No          | Historial de pagos                   |
| observaciones  | Text      | No          | Observaciones                        |
| xmlContent     | Text      | No          | Contenido XML SUNAT                  |
| hashFirma      | String    | No          | Hash de firma digital                |
| qrCode         | String    | No          | Código QR                            |
| cdr            | String    | No          | Constancia de recepción              |
| enviadoEmail   | Boolean   | Sí          | Indica si fue enviado por email      |
| archivos       | UUID[]    | No          | IDs de archivos adjuntos             |
| metadata       | JSON      | No          | Metadatos adicionales                |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 10. Entidades de Archivos

### 10.1 Archivo
**Descripción:** Archivo digital almacenado en el sistema.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| nombre         | String    | Sí          | Nombre interno                       |
| nombreOriginal | String    | Sí          | Nombre original del archivo          |
| extension      | String    | Sí          | Extensión del archivo                |
| mimeType       | String    | Sí          | Tipo MIME                            |
| tamano         | Integer   | Sí          | Tamaño en bytes                      |
| ruta           | String    | Sí          | Ruta de almacenamiento               |
| url            | String    | Sí          | URL de acceso                        |
| checksum       | String    | Sí          | Hash de verificación                 |
| entidad        | String    | Sí          | Entidad asociada                     |
| entidadId      | UUID      | Sí          | ID de la entidad asociada            |
| tipo           | Enum      | Sí          | DOCUMENTO, FOTO, PLANO, REPORTE, etc.|
| categoria      | String    | No          | Categoría del archivo                |
| tags           | String[]  | No          | Etiquetas                            |
| version        | Integer   | Sí          | Número de versión                    |
| activo         | Boolean   | Sí          | Indica si está activo                |
| subidoPor      | UUID      | Sí          | ID del usuario que subió             |
| metadata       | JSON      | No          | Metadatos EXIF, IPTC, etc.           |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 11. Entidades de Auditoría

### 11.1 Auditoria
**Descripción:** Registro de eventos y cambios en el sistema.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| usuarioId      | UUID      | No          | ID del usuario (si aplica)           |
| accion         | String    | Sí          | Acción realizada                     |
| entidad        | String    | Sí          | Entidad afectada                     |
| entidadId      | UUID      | No          | ID de la entidad                     |
| cambios        | JSON      | No          | Campos modificados                   |
| antes          | JSON      | No          | Estado anterior                      |
| despues        | JSON      | No          | Estado posterior                     |
| ip             | String    | No          | Dirección IP                         |
| userAgent      | String    | No          | User agent del navegador             |
| timestamp      | DateTime  | Sí          | Fecha y hora del evento              |
| exito          | Boolean   | Sí          | Indica si la acción fue exitosa      |
| mensaje        | String    | No          | Mensaje adicional                    |
| metadata       | JSON      | No          | Metadatos adicionales                |

---

## 12. Entidades de Configuración

### 12.1 Configuracion
**Descripción:** Parámetros configurables del sistema.

| Atributo       | Tipo      | Obligatorio | Descripción                          |
|----------------|-----------|-------------|--------------------------------------|
| id             | UUID      | Sí          | Identificador único                  |
| clave          | String    | Sí          | Clave única de configuración         |
| valor          | JSON      | Sí          | Valor de configuración               |
| tipo           | Enum      | Sí          | STRING, NUMBER, BOOLEAN, JSON, etc.  |
| descripcion    | String    | No          | Descripción                          |
| categoria      | String    | No          | Categoría                            |
| editable       | Boolean   | Sí          | Indica si es editable por usuario    |
| requiereReinicio| Boolean  | Sí          | Requiere reinicio para aplicar       |
| version        | Integer   | Sí          | Número de versión                    |
| actualizadoPor | UUID      | No          | ID del último actualizador           |
| actualizadoEn  | DateTime  | No          | Fecha de última actualización        |
| createdAt      | DateTime  | Sí          | Fecha de creación                    |
| updatedAt      | DateTime  | Sí          | Fecha de última actualización        |

---

## 13. Relaciones entre Entidades

### 13.1 Matriz de Relaciones

| Entidad Origen      | Cardinalidad | Entidad Destino     | Tipo Relación | Descripción                              |
|---------------------|--------------|---------------------|---------------|------------------------------------------|
| Usuario             | 1:N          | Auditoria           | Uno-a-muchos  | Un usuario genera múltiples auditorías   |
| Usuario             | 1:1          | Perfil              | Uno-a-uno     | Un usuario tiene un perfil               |
| Usuario             | M:N          | Rol                 | Muchos-a-muchos| Un usuario tiene múltiples roles         |
| Rol                 | M:N          | Permiso             | Muchos-a-muchos| Un rol contiene múltiples permisos       |
| Cliente             | 1:N          | Contacto            | Uno-a-muchos  | Un cliente tiene múltiples contactos     |
| Cliente             | 1:N          | Unidad              | Uno-a-muchos  | Un cliente posee múltiples unidades      |
| Cliente             | 1:N          | Orden               | Uno-a-muchos  | Un cliente genera múltiples órdenes      |
| Cliente             | 1:N          | Cotizacion          | Uno-a-muchos  | Un cliente recibe múltiples cotizaciones |
| Cliente             | 1:N          | Factura             | Uno-a-muchos  | Un cliente recibe múltiples facturas     |
| Unidad              | 1:N          | Mantenimiento       | Uno-a-muchos  | Una unidad tiene múltiples mantenimientos|
| Unidad              | 1:N          | Seguro              | Uno-a-muchos  | Una unidad tiene múltiples seguros       |
| Unidad              | 1:N          | Orden               | Uno-a-muchos  | Una unidad genera múltiples órdenes      |
| Unidad              | 1:N          | HistorialServicio   | Uno-a-muchos  | Una unidad tiene historial de servicios  |
| Categoria           | 1:N          | Categoria           | Autoreferencial | Categorías padre-hijo jerárquicas       |
| Categoria           | 1:N          | Orden               | Uno-a-muchos  | Una categoría clasifica múltiples órdenes|
| Orden               | 1:N          | ItemOrden           | Uno-a-muchos  | Una orden contiene múltiples ítems       |
| Orden               | 1:1          | Bitacora            | Uno-a-uno     | Una orden tiene una bitácora             |
| Orden               | 1:1          | Cotizacion          | Uno-a-uno     | Una orden puede generar una cotización   |
| Orden               | 1:1          | Factura             | Uno-a-uno     | Una orden puede generar una factura      |
| Cotizacion          | 1:N          | ItemCotizacion      | Uno-a-muchos  | Una cotización contiene múltiples ítems  |
| Archivo             | N:1          | Entidad (cualquiera)| Polimórfica   | Un archivo pertenece a cualquier entidad |

---

## 14. Reglas de Integridad del Dominio

### 14.1 Reglas Generales

| Código       | Descripción                                              | Entidades Afectadas              |
|--------------|----------------------------------------------------------|----------------------------------|
| RI-GEN-001   | Toda entidad debe tener un ID único UUID                 | Todas                            |
| RI-GEN-002   | Toda entidad debe tener createdAt y updatedAt            | Todas                            |
| RI-GEN-003   | Los campos email deben ser únicos cuando corresponda     | Usuario, Cliente, Contacto       |
| RI-GEN-004   | Los números correlativos deben ser únicos por tipo       | Orden, Cotizacion, Factura       |
| RI-GEN-005   | Las fechas de fin no pueden ser anteriores a fechas inicio| Orden, Cotizacion, Factura, Seguro|

### 14.2 Reglas Específicas

| Código       | Descripción                                              | Entidades Afectadas              |
|--------------|----------------------------------------------------------|----------------------------------|
| RI-CLI-001   | Un cliente debe tener al menos un método de contacto     | Cliente                          |
| RI-UNI-001   | El kilometraje no puede ser negativo                     | Unidad                           |
| RI-UNI-002   | El año de fabricación no puede ser futuro                | Unidad                           |
| RI-CAT-001   | Una categoría no puede ser su propio padre               | Categoria                        |
| RI-CAT-002   | No se puede eliminar una categoría con hijos             | Categoria                        |
| RI-ORD-001   | Una orden debe tener al menos un ítem                    | Orden, ItemOrden                 |
| RI-ORD-002   | El costo real no puede ser menor a cero                  | Orden                            |
| RI-COT-001   | Una cotización debe tener al menos un ítem               | Cotizacion, ItemCotizacion       |
| RI-COT-002   | La fecha de vencimiento debe ser posterior a emisión     | Cotizacion                       |
| RI-FAC-001   | Una factura debe tener al menos un ítem o estar vinculada a orden | Factura            |
| RI-FAC-002   | El total debe igualar subtotal + IGV - descuento         | Factura, Cotizacion              |
| RI-ARC-001   | El checksum debe validar la integridad del archivo       | Archivo                          |
| RI-AUD-001   | Toda acción crítica debe generar un registro de auditoría| Auditoria                        |

---

## 15. Enums y Tipos Personalizados

### 15.1 Estados Comunes

```typescript
enum Estado {
  ACTIVO = 'ACTIVO',
  INACTIVO = 'INACTIVO',
  SUSPENDIDO = 'SUSPENDIDO',
  ELIMINADO = 'ELIMINADO'
}
```

### 15.2 Estados de Orden

```typescript
enum EstadoOrden {
  BORRADOR = 'BORRADOR',
  PENDIENTE = 'PENDIENTE',
  EN_PROGRESO = 'EN_PROGRESO',
  EN_ESPERA_REPUESTO = 'EN_ESPERA_REPUESTO',
  EN_ESPERA_APROBACION = 'EN_ESPERA_APROBACION',
  COMPLETADA = 'COMPLETADA',
  ENTREGADA = 'ENTREGADA',
  CANCELADA = 'CANCELADA'
}
```

### 15.3 Estados de Cotización

```typescript
enum EstadoCotizacion {
  BORRADOR = 'BORRADOR',
  ENVIADA = 'ENVIADA',
  VISUALIZADA = 'VISUALIZADA',
  APROBADA = 'APROBADA',
  RECHAZADA = 'RECHAZADA',
  EXPIRADA = 'EXPIRADA',
  CONVERTIDA = 'CONVERTIDA'
}
```

### 15.4 Estados de Factura

```typescript
enum EstadoFactura {
  BORRADOR = 'BORRADOR',
  EMITIDA = 'EMITIDA',
  ENVIADA_SUNAT = 'ENVIADA_SUNAT',
  ACEPTADA = 'ACEPTADA',
  RECHAZADA = 'RECHAZADA',
  ANULADA = 'ANULADA',
  PAGADA = 'PAGADA',
  PARCIAL = 'PARCIAL',
  VENCIDA = 'VENCIDA'
}
```

### 15.5 Tipos de Cliente

```typescript
enum TipoCliente {
  NATURAL = 'NATURAL',
  JURIDICO = 'JURIDICO'
}
```

### 15.6 Tipos de Unidad

```typescript
enum TipoUnidad {
  VEHICULO = 'VEHICULO',
  MAQUINARIA = 'MAQUINARIA',
  EQUIPO = 'EQUIPO',
  HERRAMIENTA = 'HERRAMIENTA'
}
```

### 15.7 Tipos de Mantenimiento

```typescript
enum TipoMantenimiento {
  PREVENTIVO = 'PREVENTIVO',
  CORRECTIVO = 'CORRECTIVO',
  PREDICTIVO = 'PREDICTIVO',
  EVOLUTIVO = 'EVOLUTIVO'
}
```

### 15.8 Tipos de Ítem

```typescript
enum TipoItem {
  MANO_OBRA = 'MANO_OBRA',
  REPUESTO = 'REPUESTO',
  SERVICIO = 'SERVICIO',
  MATERIAL = 'MATERIAL',
  OTRO = 'OTRO'
}
```

### 15.9 Prioridades

```typescript
enum Prioridad {
  BAJA = 'BAJA',
  MEDIA = 'MEDIA',
  ALTA = 'ALTA',
  CRITICA = 'CRITICA'
}
```

### 15.10 Tipos de Documento Tributario

```typescript
enum TipoDocumentoTributario {
  FACTURA = 'FACTURA',
  BOLETA = 'BOLETA',
  NOTA_CREDITO = 'NOTA_CREDITO',
  NOTA_DEBITO = 'NOTA_DEBITO',
  TICKET = 'TICKET',
  GUIA_REMISION = 'GUIA_REMISION'
}
```

### 15.11 Tipos de Archivo

```typescript
enum TipoArchivo {
  DOCUMENTO = 'DOCUMENTO',
  FOTO = 'FOTO',
  VIDEO = 'VIDEO',
  AUDIO = 'AUDIO',
  PLANO = 'PLANO',
  REPORTE = 'REPORTE',
  CERTIFICADO = 'CERTIFICADO',
  GARANTIA = 'GARANTIA',
  MANUAL = 'MANUAL',
  OTRO = 'OTRO'
}
```

---

## 16. Trazabilidad con Requerimientos

| Entidad            | RF Asociados                                    | RNF Asociados              |
|--------------------|------------------------------------------------|----------------------------|
| Usuario, Rol, Permiso | RF-SEG-001, RF-SEG-002, RF-SEG-003, RF-SEG-004 | RNF-SEG-001, RNF-SEG-002   |
| Cliente, Contacto  | RF-CLI-001, RF-CLI-002, RF-CLI-003, RF-CLI-004 | RNF-DAT-001                |
| Unidad             | RF-UNI-001, RF-UNI-002, RF-UNI-003, RF-UNI-004 | RNF-DAT-002                |
| Categoria          | RF-CAT-001, RF-CAT-002, RF-CAT-003             | RNF-US A-001                |
| Orden, ItemOrden   | RF-ORD-001 a RF-ORD-010                        | RNF-REN-001, RNF-DIS-001   |
| HistorialServicio  | RF-HIS-001, RF-HIS-002, RF-HIS-003             | RNF-DAT-003                |
| Cotizacion         | RF-COT-001 a RF-COT-006                        | RNF-DAT-004                |
| Factura            | RF-FAC-001 a RF-FAC-008                        | RNF-SEG-003, RNF-COM-001   |
| Archivo            | RF-ARC-001 a RF-ARC-005                        | RNF-DAT-005, RNF-ESC-001   |
| Auditoria          | RF-AUD-001, RF-AUD-002, RF-AUD-003             | RNF-SEG-004                |
| Configuracion      | RF-CON-001, RF-CON-002, RF-CON-003             | RNF-MAN-001                |

---

## 17. Apéndices

### 17.1 Glosario de Términos Técnicos

| Término      | Definición                                           |
|--------------|------------------------------------------------------|
| UUID         | Identificador Único Universal (128 bits)             |
| Hash         | Resultado de función criptográfica unidireccional    |
| JSON         | JavaScript Object Notation, formato de intercambio   |
| API REST     | Interfaz de programación arquitectural RESTful       |
| Polimórfica  | Relación que puede apuntar a múltiples tipos         |
| checksum     | Valor de verificación de integridad de datos         |

### 17.2 Convenciones de Nomenclatura

- **Entidades:** PascalCase (ej: `OrdenServicio`)
- **Atributos:** camelCase (ej: `fechaEmision`)
- **Tablas BD:** snake_case plural (ej: `ordenes_servicio`)
- **IDs:** Siempre `id` como primary key
- **Foreign Keys:** `{entidad}Id` (ej: `clienteId`)
- **Timestamps:** `createdAt`, `updatedAt`, `deletedAt` (soft delete)

---

## 18. Aprobaciones

| Rol               | Nombre | Firma | Fecha | Estado     |
|-------------------|--------|-------|-------|------------|
| Arquitecto SW     |        |       |       | Pendiente  |
| Líder de Proyecto |        |       |       | Pendiente  |
| Stakeholder       |        |       |       | Pendiente  |

---

**Fin del Documento**
