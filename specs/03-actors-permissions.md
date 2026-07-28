# Actores, Roles y Permisos — Sistema de Mantenimiento Preventivo para Unidades

> **Archivo:** `03-actors-permissions.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Metodología:** Specification-Driven Development (SDD)  
> **Documento maestro:** `PLAN_DESARROLLO_SDD_MANTENIMIENTO_UNIDADES.md`

---

## 1. Actores del Sistema

| Actor | Descripción | Tipo de Usuario |
|---|---|---|
| Administrador | Usuario con privilegios completos sobre el sistema. | Interno |
| Personal administrativo | Usuario encargado de gestión operativa y administrativa. | Interno |
| Personal técnico | Usuario encargado de realizar mantenimientos y diagnósticos. | Interno |
| Consulta/Supervisión | Usuario que solo visualiza información y reportes. | Interno |

---

## 2. Roles Definidos

### 2.1 Rol: Administrador

**Descripción:** Usuario con acceso completo a todas las funcionalidades del sistema, incluyendo configuración, administración de usuarios y autorización de cambios críticos.

**Responsabilidades:**
- Gestionar usuarios, roles y permisos.
- Acceder a todos los módulos sin restricciones.
- Configurar catálogos y parámetros del sistema.
- Consultar todos los reportes.
- Administrar respaldos y configuraciones operativas.
- Autorizar cambios críticos (reapertura de órdenes, eliminaciones, etc.).

**Permisos asociados:** Todos los permisos del sistema.

---

### 2.2 Rol: Personal Administrativo

**Descripción:** Usuario encargado de la gestión operativa diaria, incluyendo clientes, unidades, cotizaciones y solicitudes de facturación.

**Responsabilidades:**
- Gestionar clientes (crear, editar, consultar).
- Gestionar unidades (crear, editar, consultar).
- Elaborar cotizaciones.
- Crear solicitudes de facturación.
- Consultar servicios e historiales.
- Generar reportes administrativos.

**Permisos asociados:**
- `customers.*` (crear, leer, actualizar)
- `vehicles.*` (crear, leer, actualizar)
- `quotations.*` (crear, leer, actualizar)
- `billing_requests.*` (crear, leer)
- `maintenance_orders.read`
- `reports.*`
- `exports.basic`

---

### 2.3 Rol: Personal Técnico

**Descripción:** Usuario encargado de realizar diagnósticos, mantenimientos y registrar evidencias técnicas.

**Responsabilidades:**
- Consultar clientes y unidades.
- Registrar inspecciones y diagnósticos.
- Registrar mantenimientos preventivos y correctivos.
- Agregar trabajos, refacciones y observaciones técnicas.
- Adjuntar evidencias fotográficas.
- Actualizar estados operativos permitidos de las órdenes.

**Permisos asociados:**
- `customers.read`
- `vehicles.read`
- `maintenance_orders.create`
- `maintenance_orders.update` (solo campos técnicos)
- `maintenance_orders.diagnose`
- `attachments.*`
- `reports.limited`

---

### 2.4 Rol: Consulta/Supervisión

**Descripción:** Usuario que únicamente puede visualizar información del sistema sin capacidad de modificación.

**Responsabilidades:**
- Visualizar clientes, unidades, mantenimientos y cotizaciones.
- Consultar reportes y dashboards.
- No modificar información crítica.

**Permisos asociados:**
- `customers.read`
- `vehicles.read`
- `maintenance_orders.read`
- `quotations.read`
- `billing_requests.read`
- `reports.read`

---

## 3. Matriz de Permisos Detallada

| Permiso | Descripción | Admin | Adminvo | Técnico | Consulta |
|---|---|:---:|:---:|:---:|:---:|
| `users.*` | Gestionar usuarios | Sí | No | No | No |
| `roles.*` | Gestionar roles y permisos | Sí | No | No | No |
| `customers.create` | Crear clientes | Sí | Sí | No | No |
| `customers.update` | Editar clientes | Sí | Sí | No | No |
| `customers.delete` | Eliminar clientes | Sí | No | No | No |
| `customers.read` | Consultar clientes | Sí | Sí | Sí | Sí |
| `vehicles.create` | Crear unidades | Sí | Sí | No | No |
| `vehicles.update` | Editar unidades | Sí | Sí | No | No |
| `vehicles.delete` | Eliminar unidades | Sí | No | No | No |
| `vehicles.read` | Consultar unidades | Sí | Sí | Sí | Sí |
| `maintenance_orders.create` | Crear órdenes | Sí | Sí | Sí | No |
| `maintenance_orders.update` | Editar órdenes | Sí | Limitado | Limitado | No |
| `maintenance_orders.diagnose` | Registrar diagnóstico | Sí | No | Sí | No |
| `maintenance_orders.add_items` | Agregar servicios/refacciones | Sí | Limitado | Sí | No |
| `maintenance_orders.change_status` | Cambiar estado de orden | Sí | Según transición | Según transición | No |
| `maintenance_orders.reopen` | Reabrir orden terminada | Sí | No | No | No |
| `maintenance_orders.delete` | Eliminar orden | Sí | No | No | No |
| `maintenance_orders.read` | Consultar órdenes | Sí | Sí | Sí | Sí |
| `quotations.create` | Crear cotizaciones | Sí | Sí | Limitado | No |
| `quotations.update` | Editar cotizaciones | Sí | Sí | No | No |
| `quotations.approve_discount` | Autorizar descuentos | Sí | Según límite | No | No |
| `quotations.read` | Consultar cotizaciones | Sí | Sí | Sí | Sí |
| `billing_requests.create` | Crear solicitudes de facturación | Sí | Sí | No | No |
| `billing_requests.update` | Editar solicitudes | Sí | Sí | No | No |
| `billing_requests.read` | Consultar solicitudes | Sí | Sí | Sí | Sí |
| `reports.full` | Consultar reportes completos | Sí | Sí | No | No |
| `reports.limited` | Consultar reportes limitados | Sí | Sí | Sí | Sí |
| `exports.full` | Exportar información completa | Sí | Sí | No | No |
| `exports.basic` | Exportar información básica | Sí | Sí | Limitado | Según permiso |
| `attachments.*` | Gestionar archivos adjuntos | Sí | Sí | Sí | No |
| `audit.view` | Ver auditoría | Sí | Limitado | No | No |
| `settings.*` | Configurar parámetros | Sí | No | No | No |

---

## 4. Reglas de Autorización

### 4.1 Principios Generales

- **Mínimo privilegio:** Cada rol tiene solo los permisos necesarios para sus funciones.
- **Segregación de funciones:** Operaciones críticas requieren roles específicos.
- **Backend como fuente de verdad:** Todas las verificaciones se realizan en el servidor.
- **UI adaptativa:** El frontend oculta acciones no autorizadas.

### 4.2 Transiciones de Estado Permitidas por Rol

| Estado Origen | Estado Destino | Roles Permitidos |
|---|---|---|
| `received` → `diagnosing` | Admin, Administrativo, Técnico |
| `diagnosing` → `pending_approval` | Admin, Técnico |
| `pending_approval` → `approved` | Admin, Administrativo |
| `approved` → `in_progress` | Admin, Técnico |
| `in_progress` → `completed` | Admin, Técnico |
| `completed` → `delivered` | Admin, Administrativo |
| Cualquier estado → `cancelled` | Admin |
| `delivered` → `received` (reabrir) | Solo Admin |

### 4.3 Límites de Descuento por Rol

| Rol | Descuento Máximo (%) | Requiere Aprobación |
|---|---|---|
| Administrador | Sin límite | No |
| Administrativo | Hasta 15% | > 10% requiere confirmación |
| Técnico | 0% | Siempre requiere aprobación |
| Consulta | N/A | N/A |

---

## 5. Implementación Técnica

### 5.1 Mecanismos de Autorización

| Mecanismo | Uso | Ejemplo |
|---|---|---|
| Policies | Autorización a nivel de modelo | `MaintenanceOrderPolicy@update` |
| Gates | Capacidades transversales | `Gate::allows('approve-discount', $amount)` |
| Middleware | Protección de rutas | `middleware('can:create-customers')` |
| Directivas Blade | Control en vistas | `@can('delete', $customer)` |
| Composables Vue | Control en componentes | `usePermission('vehicles.create')` |

### 5.2 Estructura de Permisos en Base de Datos

```
permissions
├── id
├── name (unique)
├── display_name
├── description
└── timestamps

roles
├── id
├── name (unique)
├── display_name
├── description
└── timestamps

role_user
├── role_id
└── user_id

permission_role
├── permission_id
└── role_id
```

### 5.3 Registro de Intentos Denegados

Los siguientes eventos deben registrarse en el log de auditoría:

- Intento de acceso a módulo sin permiso.
- Intento de realizar acción no autorizada.
- Intento de cambiar estado fuera de transición permitida.
- Intento de aplicar descuento superior al límite.

---

## 6. Escenarios de Uso

### 6.1 Escenario: Crear Orden de Mantenimiento

**Actores permitidos:** Administrador, Administrativo, Técnico

**Flujo:**
1. Usuario navega a módulo de órdenes.
2. Sistema verifica permiso `maintenance_orders.create`.
3. Si tiene permiso, muestra formulario.
4. Al guardar, Policy verifica datos adicionales (unidad válida, cliente activo).

### 6.2 Escenario: Aprobar Descuento

**Actores permitidos:** Administrador, Administrativo (con límites)

**Flujo:**
1. Usuario intenta aplicar descuento del 20%.
2. Sistema verifica rol y límite configurado.
3. Si excede límite, requiere aprobación de administrador.
4. Administrador aprueba o rechaza.

### 6.3 Escenario: Reabrir Orden Entregada

**Actores permitidos:** Solo Administrador

**Flujo:**
1. Usuario solicita reabrir orden `delivered`.
2. Sistema verifica que usuario tenga rol Administrador.
3. Si no es administrador, deniega y registra intento.
4. Si es administrador, permite cambio de estado y registra auditoría.

---

## 7. Trazabilidad con Documento Maestro

| Sección | Referencia en Plan Maestro |
|---|---|
| Roles | Sección 7.1 |
| Matriz de permisos | Sección 7.2 |
| Implementación | Sección 7.3 |

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
