# 04. Requerimientos Funcionales

> **Archivo:** `specs/04-functional-requirements.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Trazabilidad:** Plan de Desarrollo SDD — Sección 12 (Historias de usuario prioritarias) y Sección 11 (Especificaciones por módulo)

---

## 1. Propósito

Este documento describe los requerimientos funcionales del Sistema de Mantenimiento Preventivo para Unidades, organizados por módulo y priorizados según el valor de negocio. Cada requerimiento incluye identificador único, descripción, criterios de aceptación y trazabilidad hacia los objetivos del producto.

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

## 3. Organización de requerimientos

Los requerimientos funcionales se agrupan en las siguientes categorías:

- **RF-SEG**: Seguridad y control de acceso
- **RF-CLI**: Clientes
- **RF-UNI**: Unidades
- **RF-CAT**: Catálogos (servicios, conceptos, refacciones)
- **RF-ORD**: Órdenes de mantenimiento
- **RF-HIS**: Historial y expediente digital
- **RF-COT**: Cotizaciones
- **RF-FAC**: Solicitudes de facturación
- **RF-REP**: Reportes, búsquedas y dashboard
- **RF-AUD**: Auditoría y trazabilidad
- **RF-ARC**: Archivos adjuntos y evidencias
- **RF-CON**: Configuración general

---

## 4. Requerimientos por módulo

### 4.1 RF-SEG — Seguridad y control de acceso

#### RF-SEG-001 — Autenticación de usuarios

**Descripción:** El sistema permitirá a los usuarios autenticarse mediante credenciales (email y contraseña) gestionadas por Laravel Jetstream.

**Prioridad:** Crítica

**Criterios de aceptación:**
- El usuario podrá ingresar su email y contraseña.
- El sistema validará las credenciales contra la base de datos.
- Las contraseñas estarán encriptadas usando bcrypt.
- Tras autenticación exitosa, el usuario será redirigido al dashboard.
- Tras fallo de autenticación, se mostrará mensaje genérico de error.
- El sistema cerrará sesión tras inactividad configurable.

**Trazabilidad:** Objetivo específico 7, Módulo 1

---

#### RF-SEG-002 — Gestión de roles y permisos

**Descripción:** El sistema permitirá asignar roles a usuarios y definir permisos granulares por acción.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Existirán al menos cuatro roles predefinidos: Administrador, Administrativo, Técnico y Consulta.
- El administrador podrá crear, editar y eliminar roles personalizados.
- Cada permiso estará asociado a una acción específica (ej. `customers.create`, `orders.approve`).
- Los permisos se validarán en el backend antes de ejecutar cualquier acción.
- La interfaz ocultará acciones no autorizadas según el rol del usuario.

**Trazabilidad:** Objetivo específico 7, Módulo 1, Matriz de permisos (Sección 7.2)

---

#### RF-SEG-003 — Recuperación de contraseña

**Descripción:** El sistema permitirá a los usuarios solicitar un enlace de recuperación de contraseña vía correo electrónico.

**Prioridad:** Alta

**Criterios de aceptación:**
- El usuario podrá solicitar restablecimiento ingresando su email registrado.
- El sistema enviará un enlace temporal válido por tiempo limitado.
- El enlace permitirá establecer una nueva contraseña.
- El enlace caducará después de su uso o tras expirar el tiempo límite.

**Trazabilidad:** Objetivo específico 7, Módulo 1

---

#### RF-SEG-004 — Cierre de sesión seguro

**Descripción:** El sistema permitirá cerrar sesión de forma segura invalidando el token o sesión activa.

**Prioridad:** Media

**Criterios de aceptación:**
- El usuario podrá cerrar sesión desde cualquier página.
- Tras cerrar sesión, el usuario será redirigido al login.
- No será posible acceder a páginas protegidas sin autenticación.
- Las sesiones antiguas quedarán invalidadas.

**Trazabilidad:** Objetivo específico 7, Módulo 1

---

### 4.2 RF-CLI — Clientes

#### RF-CLI-001 — Registro de clientes

**Descripción:** El sistema permitirá registrar clientes nuevos, distinguiendo entre personas físicas y morales.

**Prioridad:** Crítica

**Criterios de aceptación:**
- El formulario diferenciará entre persona física y moral.
- Para persona física: nombre completo, teléfono, email.
- Para persona moral: razón social, nombre comercial, RFC, teléfono, email.
- El sistema validará que el email sea único cuando se proporcione.
- El cliente quedará activo por defecto.
- El registro incluirá fecha, hora y usuario creador.

**Trazabilidad:** Objetivo específico 1, Módulo 2

---

#### RF-CLI-002 — Edición de clientes

**Descripción:** El sistema permitirá modificar los datos de un cliente existente.

**Prioridad:** Alta

**Criterios de aceptación:**
- Solo usuarios con permiso podrán editar clientes.
- El sistema conservará el historial de cambios críticos.
- No se permitirá desactivar un cliente con unidades activas asociadas.
- Los campos obligatorios se validarán antes de guardar.

**Trazabilidad:** Objetivo específico 1, Módulo 2

---

#### RF-CLI-003 — Consulta de clientes

**Descripción:** El sistema permitirá buscar y visualizar clientes registrados.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Existirá un listado de clientes con paginación.
- Se podrá filtrar por nombre, razón social, email o estatus.
- La vista de detalle mostrará datos generales, perfil fiscal y unidades asociadas.
- El listado ordenará por nombre o razón social de forma predeterminada.

**Trazabilidad:** Objetivo específico 7, Módulo 2

---

#### RF-CLI-004 — Perfil fiscal de cliente

**Descripción:** El sistema permitirá registrar uno o más perfiles fiscales por cliente para facturación.

**Prioridad:** Alta

**Criterios de aceptación:**
- Un cliente podrá tener múltiples perfiles fiscales.
- Cada perfil incluirá: razón social, RFC, régimen fiscal, uso de CFDI, código postal, email fiscal.
- Un perfil podrá marcarse como predeterminado.
- Los códigos fiscales se validarán contra catálogo SAT cuando esté disponible.
- El perfil fiscal se utilizará al crear solicitudes de facturación.

**Trazabilidad:** Objetivo específico 6, Módulo 2, RN-GEN-003

---

#### RF-CLI-005 — Activación/desactivación de clientes

**Descripción:** El sistema permitirá activar o desactivar clientes según corresponda.

**Prioridad:** Media

**Criterios de aceptación:**
- Solo administradores y personal administrativo podrán cambiar el estatus.
- Un cliente desactivado no podrá asociarse a nuevas unidades u órdenes.
- El historial de un cliente desactivado permanecerá consultable.
- El cambio de estatus registrará auditoría.

**Trazabilidad:** Objetivo específico 8, Módulo 2, RN-GEN-005

---

### 4.3 RF-UNI — Unidades

#### RF-UNI-001 — Registro de unidades

**Descripción:** El sistema permitirá registrar unidades de carga pesada asociadas a un cliente.

**Prioridad:** Crítica

**Criterios de aceptación:**
- La unidad se asociará obligatoriamente a un cliente activo.
- Campos obligatorios: placas, marca, modelo, año, tipo de unidad.
- Campos opcionales: VIN, número económico, tipo de motor, kilometraje actual.
- El sistema normalizará las placas (mayúsculas, sin espacios ni guiones).
- No se permitirán placas duplicadas tras normalización.
- El VIN, cuando se proporcione, deberá ser único.

**Trazabilidad:** Objetivo específico 2, Módulo 3

---

#### RF-UNI-002 — Edición de unidades

**Descripción:** El sistema permitirá modificar los datos de una unidad existente.

**Prioridad:** Alta

**Criterios de aceptación:**
- Solo usuarios autorizados podrán editar unidades.
- El historial de cambios críticos se conservará.
- No se permitirá eliminar una unidad con órdenes de mantenimiento asociadas.
- El kilometraje actual podrá actualizarse, registrando el histórico.

**Trazabilidad:** Objetivo específico 2, Módulo 3

---

#### RF-UNI-003 — Consulta de unidades

**Descripción:** El sistema permitirá buscar y visualizar unidades registradas.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Existirá un listado de unidades con paginación.
- Se podrá filtrar por cliente, placas, número económico, marca, modelo o estatus.
- La vista de detalle mostrará identificación completa, cliente propietario y historial de servicios.
- El historial se presentará en orden cronológico descendente.

**Trazabilidad:** Objetivo específico 3, Módulo 3

---

#### RF-UNI-004 — Búsqueda rápida por placas

**Descripción:** El sistema permitirá localizar una unidad rápidamente ingresando sus placas.

**Prioridad:** Alta

**Criterios de aceptación:**
- Existirá un campo de búsqueda global accesible desde cualquier página.
- Al ingresar placas parciales o completas, el sistema sugerirá coincidencias.
- La búsqueda será insensible a mayúsculas, espacios y guiones.
- La selección redirigirá a la vista de detalle de la unidad.

**Trazabilidad:** Objetivo específico 7, Módulo 3

---

#### RF-UNI-005 — Estatus de unidades

**Descripción:** El sistema permitirá gestionar el estatus operativo de cada unidad.

**Prioridad:** Media

**Criterios de aceptación:**
- Estados posibles: Activa, En servicio, Inactiva, Baja.
- Solo usuarios autorizados podrán cambiar el estatus.
- Una unidad en servicio no podrá eliminarse.
- El cambio de estatus registrará fecha, usuario y observaciones.

**Trazabilidad:** Objetivo específico 7, Módulo 3, RN-GEN-008

---

### 4.4 RF-CAT — Catálogos

#### RF-CAT-001 — Gestión de tipos de unidad

**Descripción:** El sistema permitirá administrar un catálogo de tipos de unidad (tractocamión, remolque, plataforma, etc.).

**Prioridad:** Media

**Criterios de aceptación:**
- El administrador podrá crear, editar y desactivar tipos de unidad.
- Cada tipo tendrá nombre y descripción.
- Un tipo desactivado no podrá asignarse a nuevas unidades.
- Los tipos existentes en uso no podrán eliminarse físicamente.

**Trazabilidad:** Módulo 4

---

#### RF-CAT-002 — Gestión de categorías de servicio

**Descripción:** El sistema permitirá administrar categorías para agrupar servicios (mecánica, eléctrica, hojalatería, etc.).

**Prioridad:** Media

**Criterios de aceptación:**
- El administrador podrá crear, editar y desactivar categorías.
- Cada categoría tendrá nombre, código y descripción.
- Las categorías se usarán para filtrar y reportar servicios.
- Una categoría con servicios asociados no podrá eliminarse físicamente.

**Trazabilidad:** Módulo 4

---

#### RF-CAT-003 — Catálogo de servicios

**Descripción:** El sistema permitirá registrar servicios estandarizados que podrán incluirse en órdenes y cotizaciones.

**Prioridad:** Alta

**Criterios de aceptación:**
- Cada servicio tendrá: código, descripción, categoría, precio base, tiempo estimado, unidad de medida.
- El precio base servirá como referencia; podrá modificarse por orden o cotización.
- Los servicios podrán activarse o desactivarse.
- Un servicio desactivado no podrá seleccionarse en nuevas operaciones.
- El historial de precios se conservará en las operaciones realizadas.

**Trazabilidad:** Módulo 4, RN-GEN-003

---

#### RF-CAT-004 — Catálogo de refacciones y conceptos

**Descripción:** El sistema permitirá registrar refacciones y conceptos utilizables en órdenes y cotizaciones.

**Prioridad:** Alta

**Criterios de aceptación:**
- Cada concepto tendrá: código, descripción, tipo (refacción, mano de obra, otro), precio base, unidad de medida.
- Podrá asociarse a una categoría opcional.
- El precio base servirá como referencia.
- Los conceptos podrán activarse o desactivarse.
- El historial de precios se conservará en las operaciones realizadas.

**Trazabilidad:** Módulo 4, RN-GEN-003

---

### 4.5 RF-ORD — Órdenes de mantenimiento

#### RF-ORD-001 — Creación de órdenes de mantenimiento

**Descripción:** El sistema permitirá crear órdenes de mantenimiento preventivo o correctivo para una unidad.

**Prioridad:** Crítica

**Criterios de aceptación:**
- La orden se asociará a un cliente y una unidad.
- Se especificará el tipo: preventivo o correctivo.
- Campos obligatorios: motivo, kilometraje, fecha de recepción.
- La orden generará un folio único automático.
- El estado inicial será "Recibida".
- La orden registrará usuario creador y fecha de creación.

**Trazabilidad:** Módulo 5, RN-GEN-001

---

#### RF-ORD-002 — Registro de diagnóstico

**Descripción:** El sistema permitirá registrar el diagnóstico técnico de la orden.

**Prioridad:** Alta

**Criterios de aceptación:**
- Solo personal técnico o autorizado podrá registrar diagnóstico.
- El diagnóstico incluirá descripción detallada de hallazgos.
- Podrán agregarse notas técnicas adicionales.
- El registro conservará fecha, hora y usuario responsable.

**Trazabilidad:** Módulo 5

---

#### RF-ORD-003 — Agregar servicios a la orden

**Descripción:** El sistema permitirá agregar servicios del catálogo a la orden de mantenimiento.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Cada partida de servicio incluirá: servicio, cantidad, precio unitario, subtotal.
- El precio unitario podrá modificarse respecto al catálogo.
- El sistema calculará subtotales automáticamente.
- Se podrán agregar notas o comentarios por partida.
- Las partidas quedarán registradas con su precio histórico.

**Trazabilidad:** Módulo 5, RN-GEN-003

---

#### RF-ORD-004 — Agregar refacciones a la orden

**Descripción:** El sistema permitirá agregar refacciones y conceptos a la orden de mantenimiento.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Cada partida incluirá: concepto, cantidad, precio unitario, subtotal.
- El precio unitario podrá modificarse respecto al catálogo.
- El sistema calculará subtotales automáticamente.
- Se podrán agregar notas o comentarios por partida.
- Las partidas quedarán registradas con su precio histórico.

**Trazabilidad:** Módulo 5, RN-GEN-003

---

#### RF-ORD-005 — Cálculo de totales

**Descripción:** El sistema calculará automáticamente los totales de la orden incluyendo subtotal, descuentos, impuestos y total.

**Prioridad:** Crítica

**Criterios de aceptación:**
- El subtotal será la suma de todas las partidas.
- El descuento podrá aplicarse globalmente o por partida.
- Los impuestos se calcularán según configuración vigente.
- El total será: subtotal - descuentos + impuestos.
- Todos los cálculos se realizarán en el backend.
- Los importes se almacenarán con precisión decimal adecuada.

**Trazabilidad:** Módulo 5, RN-GEN-002, RN-GEN-009

---

#### RF-ORD-006 — Transiciones de estado de orden

**Descripción:** El sistema gestionará las transiciones de estado de una orden de mantenimiento.

**Prioridad:** Crítica

**Criterios de aceptación:**
- Estados posibles: Recibida, En diagnóstico, En proceso, Esperando autorización, Terminada, Entregada, Cancelada.
- Las transiciones seguirán reglas definidas (ej. no puede pasar de Recibida a Entregada sin pasar por En proceso).
- Cada cambio registrará fecha, hora, usuario y observaciones.
- Solo usuarios autorizados podrán realizar ciertas transiciones.
- Una orden terminada no podrá modificarse excepto por administrador.

**Trazabilidad:** Módulo 5, RN-GEN-008, Matriz de permisos

---

#### RF-ORD-007 — Adjuntar evidencias a la orden

**Descripción:** El sistema permitirá adjuntar fotografías y documentos como evidencia de la orden.

**Prioridad:** Alta

**Criterios de aceptación:**
- Se podrán subir archivos desde la interfaz de la orden.
- Tipos permitidos: imágenes (JPG, PNG), PDF, documentos Office.
- Tamaño máximo configurable por archivo.
- Los archivos se asociarán a la orden y serán consultables en el historial.
- Los archivos se validarán por tipo y contenido.

**Trazabilidad:** Módulo 13, RN-GEN-006

---

#### RF-ORD-008 — Reapertura de orden

**Descripción:** El sistema permitirá reabrir una orden terminada solo bajo autorización administrativa.

**Prioridad:** Baja

**Criterios de aceptación:**
- Solo el administrador podrá reabrir una orden terminada.
- La reapertura registrará justificación, fecha y usuario responsable.
- La orden volverá a estado "En proceso" o el que corresponda.
- El historial conservará el registro de reapertura.

**Trazabilidad:** Módulo 5, Matriz de permisos

---

### 4.6 RF-HIS — Historial y expediente digital

#### RF-HIS-001 — Historial de servicios por unidad

**Descripción:** El sistema mostrará el historial completo de servicios realizados a una unidad.

**Prioridad:** Crítica

**Criterios de aceptación:**
- El historial incluirá todas las órdenes de mantenimiento asociadas a la unidad.
- Cada registro mostrará: folio, fecha, tipo de mantenimiento, servicios realizados, total.
- El historial se ordenará por fecha descendente.
- Se podrá filtrar por rango de fechas, tipo de servicio o estado.
- Se podrá exportar el historial a PDF o Excel cuando corresponda.

**Trazabilidad:** Objetivo específico 3, Módulo 6

---

#### RF-HIS-002 — Expediente digital de unidad

**Descripción:** El sistema consolidará toda la información de una unidad en un expediente digital accesible.

**Prioridad:** Alta

**Criterios de aceptación:**
- El expediente incluirá: datos de identificación, cliente propietario, historial de servicios, cotizaciones, solicitudes de facturación y evidencias.
- La navegación será intuitiva con pestañas o secciones claras.
- El expediente será accesible desde búsquedas o listados.
- La información se presentará de forma resumida con opción de ver detalles.

**Trazabilidad:** Objetivo específico 3, Módulo 6

---

### 4.7 RF-COT — Cotizaciones

#### RF-COT-001 — Creación de cotizaciones

**Descripción:** El sistema permitirá crear cotizaciones para servicios, refacciones o mano de obra.

**Prioridad:** Alta

**Criterios de aceptación:**
- La cotización se asociará a un cliente y una unidad.
- Incluirá partidas de servicios y/o conceptos.
- Generará un folio único y número de versión.
- Estado inicial: "Borrador".
- Incluirá fecha de emisión y vigencia.
- Registrarà usuario creador y fecha.

**Trazabilidad:** Módulo 7, RN-GEN-001

---

#### RF-COT-002 — Edición de cotizaciones en borrador

**Descripción:** El sistema permitirá modificar cotizaciones mientras estén en estado "Borrador".

**Prioridad:** Alta

**Criterios de aceptación:**
- Solo usuarios autorizados podrán editar cotizaciones en borrador.
- Se podrán agregar, modificar o eliminar partidas.
- Los totales se recalcularán automáticamente.
- El historial de versiones se conservará.

**Trazabilidad:** Módulo 7

---

#### RF-COT-003 — Envío de cotización

**Descripción:** El sistema permitirá marcar una cotización como "Enviada" al cliente.

**Prioridad:** Media

**Criterios de aceptación:**
- Solo cotizaciones en borrador podrán enviarse.
- Al enviar, la cotización cambiará a estado "Enviada".
- Una vez enviada, la cotización no podrá modificarse directamente.
- El envío registrará fecha, hora y usuario responsable.

**Trazabilidad:** Módulo 7

---

#### RF-COT-004 — Aceptación de cotización

**Descripción:** El sistema permitirá registrar la aceptación de una cotización por parte del cliente.

**Prioridad:** Media

**Criterios de aceptación:**
- Solo cotizaciones en estado "Enviada" podrán aceptarse.
- La aceptación registrará fecha, hora y usuario que registra.
- Opcionalmente se podrá adjuntar comprobante de aceptación.
- La cotización aceptada podrá convertirse en orden de mantenimiento.

**Trazabilidad:** Módulo 7

---

#### RF-COT-005 — Conversión de cotización a orden

**Descripción:** El sistema permitirá convertir una cotización aceptada en una orden de mantenimiento.

**Prioridad:** Alta

**Criterios de aceptación:**
- Solo cotizaciones aceptadas podrán convertirse.
- La orden resultante heredará las partidas de la cotización.
- La orden referenciará la cotización origen.
- Los precios se conservarán según la cotización.
- La conversión registrará fecha y usuario responsable.

**Trazabilidad:** Módulo 7, Relación cotización-orden

---

#### RF-COT-006 — Versionado de cotizaciones

**Descripción:** El sistema permitirá generar nuevas versiones de una cotización cuando se requieran modificaciones posteriores al envío.

**Prioridad:** Media

**Criterios de aceptación:**
- Una cotización enviada podrá versionarse generando una nueva versión.
- La nueva versión heredará los datos de la anterior con incremento de número de versión.
- La versión anterior permanecerá consultable.
- Solo la versión más reciente podrá enviarse o aceptarse.

**Trazabilidad:** Módulo 7, RN-GEN-003

---

### 4.8 RF-FAC — Solicitudes de facturación

#### RF-FAC-001 — Creación de solicitud de facturación

**Descripción:** El sistema permitirá crear solicitudes de facturación desde una orden de mantenimiento o cotización.

**Prioridad:** Alta

**Criterios de aceptación:**
- La solicitud se asociará a una orden o cotización origen.
- Incluirá datos del cliente y perfil fiscal seleccionado.
- Incluirá desglose de conceptos a facturar.
- Generará un folio único.
- Estado inicial: "Borrador".
- Registrarà usuario creador y fecha.

**Trazabilidad:** Módulo 8, RN-GEN-001

---

#### RF-FAC-002 — Datos fiscales de la solicitud

**Descripción:** El sistema concentrará la información fiscal necesaria para emitir factura.

**Prioridad:** Alta

**Criterios de aceptación:**
- La solicitud incluirá: razón social, RFC, régimen fiscal, uso de CFDI, código postal, email fiscal.
- Los datos se obtendrán del perfil fiscal del cliente o se capturarán manualmente.
- Los datos fiscales se almacenarán como instantánea (snapshot).
- Los códigos fiscales se validarán cuando esté disponible el catálogo SAT.

**Trazabilidad:** Módulo 8, RN-GEN-003

---

#### RF-FAC-003 — Método y forma de pago

**Descripción:** El sistema permitirá registrar método y forma de pago de la solicitud.

**Prioridad:** Media

**Criterios de aceptación:**
- El usuario seleccionará método de pago (PUE, PPD, etc.).
- El usuario seleccionará forma de pago (efectivo, transferencia, tarjeta, etc.).
- Las opciones se basarán en catálogos del SAT cuando estén disponibles.
- Los datos se almacenarán en la solicitud.

**Trazabilidad:** Módulo 8

---

#### RF-FAC-004 — Estatus de solicitud de facturación

**Descripción:** El sistema gestionará los estados de una solicitud de facturación.

**Prioridad:** Media

**Criterios de aceptación:**
- Estados posibles: Borrador, Enviada a facturación, Facturada, Cancelada.
- Las transiciones seguirán reglas definidas.
- Cada cambio registrará fecha, hora, usuario y observaciones.
- Una solicitud facturada podrá registrar referencia de factura (folio fiscal, UUID).

**Trazabilidad:** Módulo 8, RN-GEN-008

---

#### RF-FAC-005 — Consulta de solicitudes de facturación

**Descripción:** El sistema permitirá consultar y filtrar solicitudes de facturación.

**Prioridad:** Media

**Criterios de aceptación:**
- Existirá un listado de solicitudes con paginación.
- Se podrá filtrar por cliente, estatus, fecha o folio.
- La vista de detalle mostrará todos los datos de la solicitud.
- Se podrá verificar si la solicitud ya fue facturada.

**Trazabilidad:** Módulo 8

---

### 4.9 RF-REP — Reportes, búsquedas y dashboard

#### RF-REP-001 — Dashboard principal

**Descripción:** El sistema mostrará un panel principal con indicadores clave de operación.

**Prioridad:** Alta

**Criterios de aceptación:**
- El dashboard mostrará: órdenes activas, órdenes terminadas este mes, cotizaciones pendientes, ingresos del periodo.
- Los indicadores se actualizarán en tiempo real o según frecuencia definida.
- El dashboard será accesible tras iniciar sesión.
- Los indicadores serán relevantes para roles administrativos y de supervisión.

**Trazabilidad:** Módulo 11, Objetivo específico 9

---

#### RF-REP-002 — Búsqueda global

**Descripción:** El sistema permitirá realizar búsquedas globales de clientes, unidades y órdenes.

**Prioridad:** Alta

**Criterios de aceptación:**
- Existirá un campo de búsqueda accesible desde cualquier página.
- La búsqueda devolverá resultados de múltiples entidades.
- Los resultados se agruparán por tipo (clientes, unidades, órdenes).
- La búsqueda será rápida y relevante.

**Trazabilidad:** Módulo 10, Objetivo específico 7

---

#### RF-REP-003 — Reporte de historial por unidad

**Descripción:** El sistema permitirá generar un reporte del historial de servicios de una unidad.

**Prioridad:** Alta

**Criterios de aceptación:**
- El reporte incluirá todas las órdenes de la unidad en un periodo.
- Se mostrarán servicios realizados, refacciones utilizadas y totales.
- El reporte podrá exportarse a PDF.
- El formato será claro y profesional.

**Trazabilidad:** Módulo 10, Objetivo específico 3

---

#### RF-REP-004 — Reporte de órdenes por periodo

**Descripción:** El sistema permitirá generar un reporte de órdenes de mantenimiento en un rango de fechas.

**Prioridad:** Media

**Criterios de aceptación:**
- El reporte filtrará órdenes por fecha de recepción o entrega.
- Incluirá folio, cliente, unidad, tipo, servicios y total.
- Permitirá agrupar por cliente, tipo de servicio o técnico.
- Podrá exportarse a Excel o PDF.

**Trazabilidad:** Módulo 10, Objetivo específico 9

---

#### RF-REP-005 — Reporte de cotizaciones

**Descripción:** El sistema permitirá generar un reporte de cotizaciones emitidas en un periodo.

**Prioridad:** Media

**Criterios de aceptación:**
- El reporte incluirá folio, cliente, unidad, estado, monto y fecha.
- Permitirá filtrar por estado (borrador, enviada, aceptada, rechazada).
- Mostrará tasa de conversión de cotizaciones a órdenes.
- Podrá exportarse a Excel o PDF.

**Trazabilidad:** Módulo 10, Objetivo específico 9

---

### 4.10 RF-AUD — Auditoría y trazabilidad

#### RF-AUD-001 — Registro de actividad crítica

**Descripción:** El sistema registrará las operaciones críticas realizadas por los usuarios.

**Prioridad:** Alta

**Criterios de aceptación:**
- Se registrarán: creación, edición, eliminación lógica, cambios de estado.
- Cada registro incluirá: usuario, fecha, hora, entidad, acción, valores anteriores y posteriores (cuando aplique).
- El registro será inmutable.
- Los administradores podrán consultar la auditoría.

**Trazabilidad:** Módulo 12, RN-GEN-004

---

#### RF-AUD-002 — Consulta de auditoría

**Descripción:** El sistema permitirá consultar el registro de auditoría por entidad o usuario.

**Prioridad:** Media

**Criterios de aceptación:**
- Existirá una interfaz para consultar registros de auditoría.
- Se podrá filtrar por entidad, usuario, fecha o acción.
- Los detalles mostrarán cambios realizados.
- El acceso estará restringido a roles autorizados.

**Trazabilidad:** Módulo 12, Matriz de permisos

---

### 4.11 RF-ARC — Archivos adjuntos y evidencias

#### RF-ARC-001 — Subida de archivos

**Descripción:** El sistema permitirá subir archivos adjuntos a diferentes entidades (unidades, órdenes, cotizaciones).

**Prioridad:** Alta

**Criterios de aceptación:**
- Los archivos se subirán mediante interfaz drag-and-drop o selector.
- Tipos permitidos configurables (imágenes, PDF, documentos).
- Tamaño máximo configurable.
- Los archivos se almacenarán de forma segura.
- La subida registrará usuario y fecha.

**Trazabilidad:** Módulo 13, RN-GEN-006

---

#### RF-ARC-002 — Visualización de archivos

**Descripción:** El sistema permitirá visualizar y descargar archivos adjuntos.

**Prioridad:** Media

**Criterios de aceptación:**
- Las imágenes podrán visualizarse en galería.
- Los PDF y documentos podrán descargarse.
- Los archivos se listarán con nombre, fecha y usuario de subida.
- Solo usuarios autorizados podrán acceder a los archivos.

**Trazabilidad:** Módulo 13

---

#### RF-ARC-003 — Eliminación de archivos

**Descripción:** El sistema permitirá eliminar archivos adjuntos cuando corresponda.

**Prioridad:** Baja

**Criterios de aceptación:**
- Solo usuarios autorizados podrán eliminar archivos.
- La eliminación será lógica (marcado como eliminado).
- El registro de auditoría conservará la referencia.
- El archivo físico podrá eliminarse según política de retención.

**Trazabilidad:** Módulo 13, RN-GEN-005

---

### 4.12 RF-CON — Configuración general

#### RF-CON-001 — Parámetros generales

**Descripción:** El sistema permitirá configurar parámetros generales de operación.

**Prioridad:** Media

**Criterios de aceptación:**
- Parámetros configurables: zona horaria, moneda, impuestos predeterminados, tamaño máximo de archivos.
- Solo administradores podrán modificar configuración.
- Los cambios registrarán auditoría.
- La configuración afectará operaciones futuras.

**Trazabilidad:** Módulo 14

---

#### RF-CON-002 — Secuencias documentales

**Descripción:** El sistema gestionará secuencias para generación de folios únicos.

**Prioridad:** Alta

**Criterios de aceptación:**
- Existirán secuencias para: órdenes, cotizaciones, solicitudes de facturación.
- Cada secuencia tendrá prefijo, longitud y contador.
- La generación de folios será transaccional y única.
- Los administradores podrán reiniciar secuencias bajo control.

**Trazabilidad:** Módulo 14, RN-GEN-001, RN-GEN-009

---

## 5. Priorización general

| Prioridad | Requerimientos |
|---|---|
| Crítica | RF-SEG-001, RF-SEG-002, RF-CLI-001, RF-CLI-003, RF-UNI-001, RF-UNI-003, RF-CAT-003, RF-CAT-004, RF-ORD-001, RF-ORD-003, RF-ORD-004, RF-ORD-005, RF-ORD-006, RF-HIS-001 |
| Alta | RF-SEG-003, RF-CLI-002, RF-CLI-004, RF-UNI-002, RF-UNI-004, RF-CAT-003, RF-CAT-004, RF-ORD-002, RF-ORD-007, RF-HIS-002, RF-COT-001, RF-COT-002, RF-COT-005, RF-FAC-001, RF-FAC-002, RF-REP-001, RF-REP-002, RF-REP-003, RF-AUD-001, RF-ARC-001, RF-CON-002 |
| Media | RF-SEG-004, RF-CLI-005, RF-UNI-005, RF-CAT-001, RF-CAT-002, RF-COT-003, RF-COT-004, RF-COT-006, RF-FAC-003, RF-FAC-004, RF-FAC-005, RF-REP-004, RF-REP-005, RF-AUD-002, RF-ARC-002, RF-CON-001 |
| Baja | RF-ORD-008, RF-ARC-003 |

---

## 6. Matriz de trazabilidad resumen

| Requerimiento | Objetivo(s) | Módulo(s) | Regla(s) de negocio |
|---|---|---|---|
| RF-SEG-001 | 7 | 1 | - |
| RF-SEG-002 | 7 | 1 | Matriz de permisos |
| RF-CLI-001 | 1 | 2 | - |
| RF-CLI-003 | 7 | 2 | - |
| RF-UNI-001 | 2 | 3 | - |
| RF-UNI-003 | 3 | 3 | - |
| RF-ORD-001 | 3, 4 | 5 | RN-GEN-001 |
| RF-ORD-005 | 4, 8 | 5 | RN-GEN-002, RN-GEN-009 |
| RF-HIS-001 | 3 | 6 | - |
| RF-COT-001 | 5 | 7 | RN-GEN-001 |
| RF-FAC-001 | 6 | 8 | RN-GEN-001 |
| RF-REP-001 | 9 | 11 | - |

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
