# 08 - Criterios de Aceptación

**Código del Documento:** SDD-CRIT-ACE-001  
**Versión:** 1.0  
**Fecha:** 2026-01-15  
**Estado:** En desarrollo  

---

## Control de Cambios

| Versión | Fecha       | Autor           | Descripción del Cambio                           | Aprobado Por |
|---------|-------------|-----------------|--------------------------------------------------|--------------|
| 1.0     | 2026-01-15  | Sistema SDD     | Creación inicial de criterios de aceptación      | Pendiente    |

---

## 1. Introducción

### 1.1 Propósito
Este documento define los criterios de aceptación para cada requerimiento funcional del sistema SSD, estableciendo las condiciones específicas que deben cumplirse para considerar un requerimiento como completado y aceptado.

### 1.2 Alcance
Cubre todos los requerimientos funcionales definidos en el documento [04-functional-requirements.md](04-functional-requirements.md).

### 1.3 Referencias
- [04-functional-requirements.md](04-functional-requirements.md) - Requerimientos Funcionales
- [05-business-rules.md](05-business-rules.md) - Reglas de Negocio
- [09-test-strategy.md](09-test-strategy.md) - Estrategia de Pruebas

### 1.4 Metodología
Los criterios de aceptación siguen el formato Gherkin (Given-When-Then) para facilitar su automatización en pruebas BDD (Behavior Driven Development).

---

## 2. Criterios por Módulo

### 2.1 Módulo de Seguridad (SEG)

#### CA-SEG-001: Autenticación de Usuarios
**RF Asociado:** RF-SEG-001  
**Descripción:** El sistema debe permitir a los usuarios autenticarse con credenciales válidas.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-SEG-001-01 | Positivo | Dado que un usuario existe con estado ACTIVO, cuando ingresa email y contraseña correctos, entonces el sistema debe permitir el acceso y redirigir al dashboard | Crítica |
| CA-SEG-001-02 | Negativo | Dado que un usuario existe, cuando ingresa contraseña incorrecta, entonces el sistema debe mostrar mensaje de error genérico "Credenciales inválidas" | Crítica |
| CA-SEG-001-03 | Negativo | Dado que un usuario no existe, cuando intenta autenticarse, entonces el sistema debe mostrar mensaje "Credenciales inválidas" sin revelar que el usuario no existe | Crítica |
| CA-SEG-001-04 | Negativo | Dado que un usuario tiene estado INACTIVO, cuando intenta autenticarse, entonces el sistema debe mostrar mensaje "Usuario inactivo, contacte al administrador" | Alta |
| CA-SEG-001-05 | Negativo | Dado que un usuario ha fallado 5 intentos consecutivos, cuando intenta autenticarse nuevamente, entonces el sistema debe bloquear la cuenta por 15 minutos | Alta |
| CA-SEG-001-06 | Borde | Dado que un usuario ingresa email con espacios en blanco, cuando se autentica, entonces el sistema debe trimitear automáticamente el email | Media |
| CA-SEG-001-07 | Seguridad | Dado que un usuario se autentica exitosamente, cuando el sistema crea la sesión, entonces la contraseña NO debe estar almacenada en la sesión en texto plano | Crítica |

**Escenarios de Prueba BDD:**

```gherkin
Scenario: Autenticación exitosa con credenciales válidas
  Given un usuario con email "admin@ssd.com" y estado "ACTIVO"
  And la contraseña encriptada es "$2b$10$..."
  When el usuario ingresa email "admin@ssd.com"
  And el usuario ingresa contraseña "Password123!"
  And presiona el botón "Ingresar"
  Then el sistema valida las credenciales correctamente
  And el sistema crea una sesión con token JWT
  And el usuario es redirigido al dashboard
  And se registra un evento de auditoría "LOGIN_EXITOSO"

Scenario: Autenticación fallida por contraseña incorrecta
  Given un usuario existente con estado "ACTIVO"
  When el usuario ingresa email válido
  And el usuario ingresa contraseña incorrecta
  And presiona el botón "Ingresar"
  Then el sistema muestra mensaje "Credenciales inválidas"
  And el sistema incrementa el contador de intentos fallidos
  And NO se revela si el email existe o no
  And se registra un evento de auditoría "LOGIN_FALLIDO"
```

**Evidencia Requerida:**
- [ ] Capturas de pantalla de flujo exitoso
- [ ] Capturas de pantalla de mensajes de error
- [ ] Logs de auditoría de intentos de login
- [ ] Reporte de pruebas de penetración básicas

---

#### CA-SEG-002: Gestión de Roles y Permisos
**RF Asociado:** RF-SEG-002  
**Descripción:** El sistema debe permitir asignar roles con permisos específicos a los usuarios.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-SEG-002-01 | Positivo | Dado que un administrador existe, cuando asigna un rol a un usuario, entonces el usuario debe heredar todos los permisos del rol | Crítica |
| CA-SEG-002-02 | Positivo | Dado que un usuario tiene múltiples roles, cuando accede al sistema, entonces debe tener la unión de todos los permisos de sus roles | Alta |
| CA-SEG-002-03 | Negativo | Dado que un usuario sin permiso CREAR_ORDEN intenta crear una orden, entonces el sistema debe denegar el acceso y mostrar mensaje "No tiene permisos suficientes" | Crítica |
| CA-SEG-002-04 | Negativo | Dado que un rol de sistema existe (ej: ADMIN), cuando un usuario intenta eliminarlo, entonces el sistema debe mostrar mensaje "Los roles del sistema no pueden eliminarse" | Alta |
| CA-SEG-002-05 | Borde | Dado que un usuario tiene dos roles con permisos contradictorios, cuando evalúa permisos, entonces debe prevalecer el permiso más permisivo | Media |

**Escenarios de Prueba BDD:**

```gherkin
Scenario: Usuario hereda permisos de rol asignado
  Given un rol "TECNICO" con permisos ["ORDEN.LEER", "ORDEN.ACTUALIZAR"]
  And un usuario "Juan" sin roles asignados
  When el administrador asigna el rol "TECNICO" al usuario "Juan"
  Then el usuario "Juan" debe tener los permisos ["ORDEN.LEER", "ORDEN.ACTUALIZAR"]
  And el usuario puede acceder a módulos de órdenes
  And se registra evento de auditoría "ROL_ASIGNADO"

Scenario: Acceso denegado por falta de permiso
  Given un usuario "Maria" con rol "CLIENTE"
  And el rol "CLIENTE" no tiene permiso "ORDEN.ELIMINAR"
  When "Maria" intenta eliminar una orden
  Then el sistema verifica permisos
  And el sistema deniega la operación
  And muestra mensaje "No tiene permisos para realizar esta acción"
  And se registra evento de auditoría "ACCESO_DENEGADO"
```

---

#### CA-SEG-003: Recuperación de Contraseña
**RF Asociado:** RF-SEG-003  
**Descripción:** El sistema debe permitir a los usuarios recuperar su contraseña mediante email.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-SEG-003-01 | Positivo | Dado que un usuario existe, cuando solicita recuperación de contraseña, entonces el sistema debe enviar un email con token de recuperación válido por 1 hora | Alta |
| CA-SEG-003-02 | Negativo | Dado que un usuario no existe, cuando solicita recuperación, entonces el sistema debe mostrar mensaje genérico "Si el email existe, recibirá instrucciones" | Alta |
| CA-SEG-003-03 | Negativo | Dado que un token de recuperación expiró, cuando el usuario intenta usarlo, entonces el sistema debe mostrar mensaje "Token expirado, solicite nueva recuperación" | Alta |
| CA-SEG-003-04 | Seguridad | Dado que un token fue usado, cuando se intenta reutilizar, entonces el sistema debe invalidarlo y mostrar mensaje "Token ya utilizado" | Crítica |
| CA-SEG-003-05 | Seguridad | Dado que un usuario cambia su contraseña, entonces la nueva contraseña debe cumplir con la política de seguridad (mínimo 8 caracteres, mayúscula, número, especial) | Crítica |

---

### 2.2 Módulo de Clientes (CLI)

#### CA-CLI-001: Registro de Cliente Natural
**RF Asociado:** RF-CLI-001  
**Descripción:** El sistema debe permitir registrar clientes naturales con información completa.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-CLI-001-01 | Positivo | Dado que un usuario con permiso existe, cuando registra un cliente natural con datos válidos, entonces el sistema debe crear el registro y generar un ID único | Crítica |
| CA-CLI-001-02 | Validación | Dado que se registra un cliente, cuando el documento ya existe para otro cliente, entonces el sistema debe mostrar error "Documento ya registrado" | Crítica |
| CA-CLI-001-03 | Validación | Dado que se registra un cliente, cuando el email ya existe, entonces el sistema debe mostrar error "Email ya registrado" | Alta |
| CA-CLI-001-04 | Validación | Dado que se registra un cliente, cuando el RUC/DNI tiene formato inválido, entonces el sistema debe validar según reglas de SUNAT | Alta |
| CA-CLI-001-05 | Positivo | Dado que un cliente se registra exitosamente, entonces el sistema debe asignar estado ACTIVO por defecto | Media |
| CA-CLI-001-06 | Auditoría | Dado que un cliente se crea, entonces se debe registrar evento de auditoría con datos antes/después | Alta |

**Escenarios de Prueba BDD:**

```gherkin
Scenario: Registro exitoso de cliente natural
  Given un usuario con permiso "CLIENTE.CREAR"
  And los siguientes datos válidos:
    | Campo           | Valor              |
    | tipo            | NATURAL            |
    | nombre          | Juan Pérez         |
    | documentoTipo   | DNI                |
    | documentoNumero | 12345678           |
    | email           | juan@email.com     |
    | telefono        | 987654321          |
  When el usuario envía el formulario de registro
  Then el sistema valida que el documento no exista
  And el sistema valida que el email no exista
  And el sistema crea el cliente con estado "ACTIVO"
  And el sistema retorna el ID del cliente creado
  And se registra evento de auditoría "CLIENTE_CREADO"

Scenario: Registro fallido por documento duplicado
  Given un cliente existente con documento "12345678"
  When se intenta registrar otro cliente con mismo documento
  Then el sistema valida la duplicidad
  And el sistema muestra error "El documento 12345678 ya está registrado"
  And NO se crea el cliente
  And el código de error es "CLIENTE_DUPLICADO"
```

---

#### CA-CLI-002: Búsqueda y Filtrado de Clientes
**RF Asociado:** RF-CLI-002  
**Descripción:** El sistema debe permitir buscar y filtrar clientes por múltiples criterios.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-CLI-002-01 | Positivo | Dado que existen clientes registrados, cuando se busca por nombre parcial, entonces el sistema debe retornar coincidencias case-insensitive | Alta |
| CA-CLI-002-02 | Positivo | Dado que existen clientes, cuando se filtra por tipo (NATURAL/JURIDICO), entonces el sistema debe retornar solo clientes del tipo seleccionado | Media |
| CA-CLI-002-03 | Positivo | Dado que existen clientes, cuando se filtra por estado, entonces el sistema debe retornar clientes activos/inactivos según filtro | Media |
| CA-CLI-002-04 | Rendimiento | Dado que existen 10,000 clientes, cuando se realiza una búsqueda, entonces la respuesta debe ser menor a 2 segundos | Alta |
| CA-CLI-002-05 | Paginación | Dado que hay más resultados que el límite por página, cuando se listan clientes, entonces el sistema debe implementar paginación con metadata | Media |

---

### 2.3 Módulo de Unidades (UNI)

#### CA-UNI-001: Registro de Unidad
**RF Asociado:** RF-UNI-001  
**Descripción:** El sistema debe permitir registrar unidades vehiculares con información técnica completa.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-UNI-001-01 | Positivo | Dado que un usuario con permiso existe, cuando registra una unidad con datos válidos, entonces el sistema debe crear el registro con código único auto-generado | Crítica |
| CA-UNI-001-02 | Validación | Dado que se registra una unidad, cuando el código interno ya existe, entonces el sistema debe mostrar error "Código de unidad ya registrado" | Crítica |
| CA-UNI-001-03 | Validación | Dado que se registra una unidad, cuando la placa ya existe para otra unidad, entonces el sistema debe mostrar advertencia pero permitir registro (placas pueden cambiar) | Media |
| CA-UNI-001-04 | Validación | Dado que se registra una unidad, cuando el año de fabricación es mayor al año actual, entonces el sistema debe mostrar error "Año no puede ser futuro" | Alta |
| CA-UNI-001-05 | Validación | Dado que se registra una unidad, cuando el kilometraje es negativo, entonces el sistema debe mostrar error "Kilometraje no puede ser negativo" | Alta |
| CA-UNI-001-06 | Negocio | Dado que se registra una unidad, cuando no se especifica cliente propietario, entonces la unidad queda como disponible/asignable | Media |

---

### 2.4 Módulo de Órdenes de Servicio (ORD)

#### CA-ORD-001: Creación de Orden de Servicio
**RF Asociado:** RF-ORD-001  
**Descripción:** El sistema debe permitir crear órdenes de servicio con información completa del servicio solicitado.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-ORD-001-01 | Positivo | Dado que un usuario con permiso existe, cuando crea una orden con cliente, unidad y categoría válidos, entonces el sistema debe generar número correlativo único | Crítica |
| CA-ORD-001-02 | Validación | Dado que se crea una orden, cuando no se especifica al menos un ítem, entonces el sistema debe mostrar error "La orden debe tener al menos un ítem" | Alta |
| CA-ORD-001-03 | Validación | Dado que se crea una orden, cuando el cliente seleccionado está INACTIVO, entonces el sistema debe mostrar advertencia pero permitir creación | Media |
| CA-ORD-001-04 | Negocio | Dado que se crea una orden, entonces el sistema debe asignar estado BORRADOR inicialmente | Alta |
| CA-ORD-001-05 | Negocio | Dado que se crea una orden, cuando se agrega un ítem, entonces el sistema debe calcular automáticamente subtotal = cantidad × precio unitario | Crítica |
| CA-ORD-001-06 | Negocio | Dado que se crea una orden, entonces el sistema debe calcular IGV (18%) sobre la suma de subtotales | Crítica |
| CA-ORD-001-07 | Auditoría | Dado que se crea una orden, entonces se debe registrar evento con toda la información de la orden creada | Alta |

**Escenarios de Prueba BDD:**

```gherkin
Scenario: Creación exitosa de orden de servicio
  Given un usuario con permiso "ORDEN.CREAR"
  And un cliente activo "Cliente ABC"
  And una unidad "Vehículo XYZ" asociada al cliente
  And una categoría "Mantenimiento Preventivo"
  When el usuario crea una orden con:
    | Campo           | Valor                    |
    | cliente         | Cliente ABC              |
    | unidad          | Vehículo XYZ             |
    | categoria       | Mantenimiento Preventivo |
    | prioridad       | MEDIA                    |
    | items           | [{"concepto": "Cambio de aceite", "cantidad": 1, "precio": 150.00}] |
  Then el sistema genera número de orden "ORD-2026-00001"
  And el sistema calcula subtotal = 150.00
  And el sistema calcula IGV = 27.00
  And el sistema calcula total = 177.00
  And la orden queda en estado "BORRADOR"
  And se registra evento de auditoría "ORDEN_CREADA"

Scenario: Creación fallida sin ítems
  Given un usuario con permiso "ORDEN.CREAR"
  When el usuario intenta crear una orden sin ítems
  Then el sistema valida que haya al menos un ítem
  And el sistema muestra error "La orden debe contener al menos un ítem de servicio"
  And NO se crea la orden
```

---

#### CA-ORD-002: Actualización de Estado de Orden
**RF Asociado:** RF-ORD-002  
**Descripción:** El sistema debe permitir actualizar el estado de una orden siguiendo el flujo establecido.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-ORD-002-01 | Positivo | Dado que una orden está en BORRADOR, cuando se envía a revisión, entonces el estado cambia a PENDIENTE | Alta |
| CA-ORD-002-02 | Positivo | Dado que una orden está en PENDIENTE, cuando un técnico inicia trabajo, entonces el estado cambia a EN_PROGRESO y se registra fecha inicio | Alta |
| CA-ORD-002-03 | Transición Inválida | Dado que una orden está en BORRADOR, cuando se intenta cambiar directamente a COMPLETADA, entonces el sistema debe rechazar la transición | Alta |
| CA-ORD-002-04 | Negocio | Dado que una orden cambia a EN_PROGRESO, cuando no había fecha de inicio registrada, entonces el sistema registra timestamp actual como fechaInicio | Media |
| CA-ORD-002-05 | Negocio | Dado que una orden cambia a COMPLETADA, cuando no había fecha de fin registrada, entonces el sistema registra timestamp actual como fechaFin | Media |
| CA-ORD-002-06 | Permisos | Dado que un usuario sin permiso ORDEN.ACTUALIZAR intenta cambiar estado, entonces el sistema debe denegar la operación | Crítica |

**Diagrama de Flujo de Estados:**

```
BORRADOR → PENDIENTE → EN_PROGRESO → COMPLETADA → ENTREGADA
              ↓              ↓
         CANCELADA     CANCELADA
```

---

#### CA-ORD-003: Asignación de Técnico a Orden
**RF Asociado:** RF-ORD-003  
**Descripción:** El sistema debe permitir asignar un técnico responsable a una orden de servicio.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-ORD-003-01 | Positivo | Dado que una orden existe, cuando se asigna un técnico con rol TÉCNICO, entonces el sistema actualiza el campo asignadoA | Alta |
| CA-ORD-003-02 | Notificación | Dado que un técnico es asignado a una orden, entonces el sistema debe enviar notificación al técnico (email/push) | Media |
| CA-ORD-003-03 | Validación | Dado que una orden ya tiene técnico asignado, cuando se reasigna a otro técnico, entonces el sistema debe registrar el cambio en bitácora | Media |
| CA-ORD-003-04 | Validación | Dado que se asigna un técnico, cuando el usuario no tiene rol TÉCNICO, entonces el sistema debe mostrar error "Solo se pueden asignar usuarios con rol técnico" | Alta |

---

### 2.5 Módulo de Cotizaciones (COT)

#### CA-COT-001: Generación de Cotización desde Orden
**RF Asociado:** RF-COT-001  
**Descripción:** El sistema debe permitir generar una cotización basada en los ítems de una orden.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-COT-001-01 | Positivo | Dado que una orden existe con ítems, cuando se genera cotización, entonces el sistema debe copiar todos los ítems con sus precios | Crítica |
| CA-COT-001-02 | Negocio | Dado que se genera cotización, entonces el sistema debe asignar número correlativo único serie COT | Crítica |
| CA-COT-001-03 | Negocio | Dado que se genera cotización, cuando no se especifica validez, entonces el sistema debe asignar 15 días por defecto | Media |
| CA-COT-001-04 | Negocio | Dado que se genera cotización, entonces la fecha de vencimiento debe calcularse como fechaEmision + validezDias | Alta |
| CA-COT-001-05 | Vinculación | Dado que una cotización se genera desde orden, entonces el sistema debe mantener referencia bidireccional ordenId ↔ cotizacionId | Alta |
| CA-COT-001-06 | Estado | Dado que se genera cotización, entonces el estado inicial debe ser BORRADOR | Media |

---

#### CA-COT-002: Aprobación de Cotización por Cliente
**RF Asociado:** RF-COT-002  
**Descripción:** El sistema debe permitir que el cliente apruebe o rechace una cotización.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-COT-002-01 | Positivo | Dado que una cotización está ENVIADA, cuando el cliente la aprueba, entonces el estado cambia a APROBADA | Alta |
| CA-COT-002-02 | Positivo | Dado que una cotización está ENVIADA, cuando el cliente la rechaza, entonces el estado cambia a RECHAZADA y se registra motivo | Alta |
| CA-COT-002-03 | Negocio | Dado que una cotización es APROBADA, entonces el sistema debe permitir convertirla a factura | Alta |
| CA-COT-002-04 | Validez | Dado que una cotización excede su fecha de vencimiento, cuando se intenta aprobar, entonces el sistema debe mostrar error "Cotización expirada" | Alta |
| CA-COT-002-05 | Trazabilidad | Dado que una cotización es aprobada/rechazada, entonces el sistema debe registrar fecha, hora y usuario que realizó la acción | Media |

---

### 2.6 Módulo de Facturación (FAC)

#### CA-FAC-001: Emisión de Factura Electrónica
**RF Asociado:** RF-FAC-001  
**Descripción:** El sistema debe emitir facturas electrónicas válidas según normativa SUNAT.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-FAC-001-01 | Positivo | Dado que una cotización APROBADA existe, cuando se convierte a factura, entonces el sistema debe generar XML según esquema UBL 2.1 de SUNAT | Crítica |
| CA-FAC-001-02 | Validación | Dado que se emite factura, cuando el cliente no tiene RUC válido, entonces el sistema debe mostrar error "Cliente debe tener RUC válido para facturación" | Crítica |
| CA-FAC-001-03 | Integración | Dado que se emite factura, cuando se envía a SUNAT, entonces el sistema debe recibir CDR (Constancia de Recepción) válido | Crítica |
| CA-FAC-001-04 | Negocio | Dado que SUNAT acepta la factura, entonces el estado debe cambiar a EMITIDA y almacenar hash de firma | Crítica |
| CA-FAC-001-05 | Negocio | Dado que SUNAT rechaza la factura, entonces el estado debe cambiar a RECHAZADA y almacenar motivo del rechazo | Crítica |
| CA-FAC-001-06 | Visualización | Dado que una factura es emitida, entonces el sistema debe generar código QR según normativa SUNAT | Alta |
| CA-FAC-001-07 | Envío | Dado que una factura es emitida, entonces el sistema debe enviar PDF + XML al email del cliente | Alta |

**Escenarios de Prueba BDD:**

```gherkin
Scenario: Emisión exitosa de factura electrónica
  Given una cotización APROBADA con total 1000.00
  And un cliente con RUC válido "20123456789"
  And configuración SUNAT válida en el sistema
  When el usuario convierte la cotización a factura
  Then el sistema genera XML UBL 2.1
  And el sistema firma digitalmente el XML
  And el sistema envía XML a SUNAT
  And SUNAT retorna CDR con código "0" (aceptado)
  And el sistema almacena hash de firma
  And el sistema genera código QR
  And el estado cambia a "EMITIDA"
  And el sistema envía email al cliente con PDF y XML
  And se registra evento de auditoría "FACTURA_EMITIDA"

Scenario: Emisión fallida por RUC inválido
  Given una cotización APROBADA
  And un cliente sin RUC o con RUC inválido
  When el usuario intenta convertir a factura
  Then el sistema valida el RUC del cliente
  And el sistema muestra error "Cliente debe tener RUC válido"
  And NO se genera la factura
  And el código de error es "FACTURA_RUC_INVALIDO"
```

---

#### CA-FAC-002: Anulación de Factura
**RF Asociado:** RF-FAC-002  
**Descripción:** El sistema debe permitir anular facturas emitidas generando nota de crédito.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-FAC-002-01 | Positivo | Dado que una factura EMITIDA existe, cuando se anula con motivo válido, entonces el sistema debe generar Nota de Crédito vinculada | Crítica |
| CA-FAC-002-02 | Validación | Dado que se anula factura, cuando no se proporciona motivo, entonces el sistema debe mostrar error "Debe especificar motivo de anulación" | Alta |
| CA-FAC-002-03 | Validación | Dado que se anula factura, cuando la factura ya estaba ANULADA, entonces el sistema debe mostrar error "Factura ya anulada" | Alta |
| CA-FAC-002-04 | Negocio | Dado que se genera Nota de Crédito, entonces debe enviarse a SUNAT y seguir mismo proceso que factura | Crítica |
| CA-FAC-002-05 | Negocio | Dado que la Nota de Crédito es aceptada, entonces la factura original cambia estado a ANULADA | Crítica |

---

### 2.7 Módulo de Historial (HIS)

#### CA-HIS-001: Consulta de Historial por Unidad
**RF Asociado:** RF-HIS-001  
**Descripción:** El sistema debe mostrar el historial completo de servicios de una unidad.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-HIS-001-01 | Positivo | Dado que una unidad existe, cuando se consulta su historial, entonces el sistema debe retornar todas las órdenes completadas asociadas | Alta |
| CA-HIS-001-02 | Ordenamiento | Dado que existen múltiples servicios, cuando se lista historial, entonces debe ordenarse por fecha descendente (más reciente primero) | Media |
| CA-HIS-001-03 | Consolidación | Dado que una unidad tiene servicios, cuando se muestra historial, entonces debe incluir costo total acumulado, último servicio, próximo servicio recomendado | Media |
| CA-HIS-001-04 | Filtros | Dado que existe historial, cuando se filtra por rango de fechas, entonces el sistema debe retornar solo servicios en ese período | Media |
| CA-HIS-001-05 | Exportación | Dado que existe historial, cuando se exporta, entonces el sistema debe generar PDF con resumen ejecutivo | Baja |

---

### 2.8 Módulo de Archivos (ARC)

#### CA-ARC-001: Subida de Archivos
**RF Asociado:** RF-ARC-001  
**Descripción:** El sistema debe permitir subir archivos adjuntos a diferentes entidades.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-ARC-001-01 | Positivo | Dado que un usuario con permiso existe, cuando sube un archivo válido, entonces el sistema debe almacenarlo y generar URL de acceso | Crítica |
| CA-ARC-001-02 | Validación | Dado que se sube un archivo, cuando excede tamaño máximo (10MB), entonces el sistema debe mostrar error "Archivo excede tamaño máximo permitido" | Alta |
| CA-ARC-001-03 | Validación | Dado que se sube un archivo, cuando la extensión no está permitida, entonces el sistema debe mostrar error "Tipo de archivo no permitido" | Alta |
| CA-ARC-001-04 | Seguridad | Dado que se sube un archivo, cuando el nombre contiene caracteres especiales, entonces el sistema debe sanitizar el nombre | Alta |
| CA-ARC-001-05 | Integridad | Dado que se sube un archivo, entonces el sistema debe calcular y almacenar checksum SHA-256 | Media |
| CA-ARC-001-06 | Versionado | Dado que se sube archivo con mismo nombre a misma entidad, entonces el sistema debe crear nueva versión incremental | Media |

---

### 2.9 Módulo de Auditoría (AUD)

#### CA-AUD-001: Registro de Eventos de Auditoría
**RF Asociado:** RF-AUD-001  
**Descripción:** El sistema debe registrar automáticamente eventos críticos de auditoría.

**Criterios de Aceptación:**

| ID | Tipo | Descripción | Prioridad |
|----|------|-------------|-----------|
| CA-AUD-001-01 | Positivo | Dado que ocurre un evento crítico (login, creación, modificación, eliminación), entonces el sistema debe registrar entrada en tabla auditoria | Crítica |
| CA-AUD-001-02 | Completitud | Dado que se registra evento, entonces debe incluir: usuarioId, acción, entidad, entidadId, timestamp, IP, userAgent | Crítica |
| CA-AUD-001-03 | Cambios | Dado que se modifica un registro, entonces el sistema debe almacenar JSON con valores antes y después | Alta |
| CA-AUD-001-04 | Inmutabilidad | Dado que un registro de auditoría existe, cuando se intenta modificar/eliminar, entonces el sistema debe denegar la operación | Crítica |
| CA-AUD-001-05 | Retención | Dado que existen registros de auditoría, cuando superan 2 años, entonces el sistema debe archivarlos (no eliminar) | Media |

---

## 3. Matriz de Trazabilidad de Criterios

| Módulo | RF | Criterios de Aceptación | Casos de Prueba Mínimos |
|--------|-----|------------------------|------------------------|
| SEG    | RF-SEG-001 | 7 | 12 |
| SEG    | RF-SEG-002 | 5 | 8 |
| SEG    | RF-SEG-003 | 5 | 6 |
| CLI    | RF-CLI-001 | 6 | 10 |
| CLI    | RF-CLI-002 | 5 | 8 |
| UNI    | RF-UNI-001 | 6 | 10 |
| ORD    | RF-ORD-001 | 7 | 12 |
| ORD    | RF-ORD-002 | 6 | 10 |
| ORD    | RF-ORD-003 | 4 | 6 |
| COT    | RF-COT-001 | 6 | 8 |
| COT    | RF-COT-002 | 5 | 7 |
| FAC    | RF-FAC-001 | 7 | 15 |
| FAC    | RF-FAC-002 | 5 | 8 |
| HIS    | RF-HIS-001 | 5 | 6 |
| ARC    | RF-ARC-001 | 6 | 10 |
| AUD    | RF-AUD-001 | 5 | 6 |

**Total:** 88 criterios de aceptación, 142 casos de prueba mínimos

---

## 4. Definición de Terminado (DoD)

Para que un requerimiento se considere completado, debe cumplir:

### 4.1 Criterios Generales
- [ ] Todos los criterios de aceptación asociados están verificados
- [ ] Código implementado y revisado por pares
- [ ] Pruebas unitarias con cobertura mínima 80%
- [ ] Pruebas de integración ejecutadas exitosamente
- [ ] Documentación actualizada
- [ ] Criterios de rendimiento validados
- [ ] Seguridad validada (OWASP Top 10)
- [ ] Accesibilidad básica verificada

### 4.2 Evidencia Requerida
- [ ] Capturas de pantalla de funcionalidades clave
- [ ] Logs de ejecución de pruebas
- [ ] Reportes de herramientas de análisis estático
- [ ] Registro de revisión de código
- [ ] Checklist de seguridad completado

---

## 5. Apéndices

### 5.1 Formato de Escenario BDD

```gherkin
Feature: Nombre de la funcionalidad
  Como [rol]
  Quiero [acción]
  Para [beneficio]

  Scenario: Título descriptivo
    Given [contexto/precondiciones]
    And [precondiciones adicionales]
    When [acción/evento]
    And [acciones adicionales]
    Then [resultado esperado]
    And [resultados adicionales]
```

### 5.2 Niveles de Prioridad

| Nivel   | Descripción                                      | Tiempo Máximo Resolución |
|---------|--------------------------------------------------|--------------------------|
| Crítica | Bloquea funcionalidad core, debe corregirse ya   | 24 horas                 |
| Alta    | Impacta significativamente, próxima release      | 1 semana                 |
| Media   | Importante pero tiene workaround                 | 2 semanas                |
| Baja    | Mejora menor, cosmético                          | Backlog                  |

---

## 6. Aprobaciones

| Rol               | Nombre | Firma | Fecha | Estado     |
|-------------------|--------|-------|-------|------------|
| Líder QA          |        |       |       | Pendiente  |
| Arquitecto SW     |        |       |       | Pendiente  |
| Product Owner     |        |       |       | Pendiente  |

---

**Fin del Documento**
