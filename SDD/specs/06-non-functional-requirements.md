# 06. Requerimientos No Funcionales

> **Archivo:** `specs/06-non-functional-requirements.md`  
> **Versión:** 1.0.0  
> **Estado:** Especificación propuesta  
> **Trazabilidad:** Plan de Desarrollo SDD — Sección 13 (Requerimientos no funcionales)

---

## 1. Propósito

Este documento especifica los requerimientos no funcionales del Sistema de Mantenimiento Preventivo para Unidades, definiendo las características de calidad, restricciones técnicas y estándares que el sistema debe cumplir. Cada requerimiento incluye identificador único, descripción, criterios de validación y métricas de aceptación.

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

## 3. Clasificación de RNF

Los requerimientos no funcionales se clasifican en las siguientes categorías:

- **RNF-SEG**: Seguridad
- **RNF-REN**: Rendimiento
- **RNF-DIS**: Disponibilidad y continuidad
- **RNF-USA**: Usabilidad
- **RNF-MAN**: Mantenibilidad
- **RNF-DAT**: Protección de datos
- **RNF-ESC**: Escalabilidad
- **RNF-COM**: Compatibilidad

---

## 4. Requerimientos por categoría

### 4.1 RNF-SEG — Seguridad

#### RNF-SEG-001 — Autenticación obligatoria

**Descripción:** Todas las rutas y recursos del sistema requerirán autenticación previa, excepto login, recuperación de contraseña y endpoints explícitamente públicos.

**Criterios de validación:**
- Middleware de autenticación aplicado globalmente.
- Excepciones documentadas y justificadas.
- Intentos de acceso no autenticado redirigen a login.
- APIs retornan 401 Unauthorized.

**Métrica:** 100% de rutas protegidas verificadas en pruebas de seguridad.

---

#### RNF-SEG-002 — Autorización en servidor

**Descripción:** La autorización se validará exclusivamente en el backend. El frontend solo ocultará acciones no permitidas como mejora de UX.

**Criterios de validación:**
- Policies y gates implementados para cada recurso.
- Pruebas de autorización para cada rol.
- Intentos denegados registran auditoría cuando corresponda.
- No existe lógica de autorización crítica solo en frontend.

**Métrica:** 100% de acciones críticas con validación backend.

---

#### RNF-SEG-003 — Protección CSRF

**Descripción:** El sistema implementará protección contra ataques Cross-Site Request Forgery en todas las mutaciones.

**Criterios de validación:**
- Tokens CSRF generados y validados en formularios.
- Headers CSRF validados en peticiones AJAX/API.
- Peticiones sin token válido son rechazadas con 419.

**Métrica:** 100% de mutaciones protegidas.

---

#### RNF-SEG-004 — Rate limiting

**Descripción:** El sistema aplicará límites de tasa en operaciones sensibles para prevenir abusos y ataques de fuerza bruta.

**Criterios de validación:**
- Login: máximo 5 intentos fallidos por minuto por IP.
- Recuperación de contraseña: máximo 3 solicitudes por hora por email.
- APIs: límites configurables según endpoint.
- Excesos retornan 429 Too Many Requests.

**Métrica:** Límites configurados y probados en entorno de staging.

---

#### RNF-SEG-005 — Sesiones seguras

**Descripción:** Las sesiones de usuario se gestionarán de forma segura con expiración, renovación y invalidación controladas.

**Criterios de validación:**
- Cookies de sesión con flags `Secure`, `HttpOnly`, `SameSite`.
- Expiración de inactividad configurable (default: 2 horas).
- Invalidación de sesión al cerrar sesión o cambiar contraseña.
- Renovación de ID de sesión tras autenticación.

**Métrica:** Configuración verificada en auditoría de seguridad.

---

#### RNF-SEG-006 — Gestión de secretos

**Descripción:** Las credenciales, claves API y secretos se almacenarán fuera del repositorio de código, utilizando variables de entorno o gestores de secretos.

**Criterios de validación:**
- `.env` no versionado en Git (incluido en `.gitignore`).
- `.env.example` con valores placeholder documentados.
- Secrets de producción en gestor de secretos o variables de entorno del servidor.
- No hay hardcoded credentials en el código.

**Métrica:** 0 secretos en repositorio verificado con herramientas de scanning.

---

#### RNF-SEG-007 — Validación de archivos subidos

**Descripción:** Los archivos subidos se validarán por tipo, tamaño y contenido para prevenir ejecución de código malicioso.

**Criterios de validación:**
- Validación de extensión y MIME type real.
- Tamaño máximo configurable por tipo de archivo.
- Almacenamiento en directorio no ejecutable.
- Sanitización de nombres de archivo.

**Métrica:** 100% de uploads validados, probado con archivos maliciosos de prueba.

---

#### RNF-SEG-008 — Registro de accesos y cambios críticos

**Descripción:** El sistema registrará accesos de usuarios y cambios críticos para fines de auditoría y detección de anomalías.

**Criterios de validación:**
- Log de accesos con timestamp, usuario, IP, user agent.
- Log de cambios críticos con before/after.
- Logs almacenados de forma segura e inmutable.
- Retención de logs según política definida (mínimo 1 año).

**Métrica:** Logs completos y consultables, retention policy documentada.

---

### 4.2 RNF-REN — Rendimiento

#### RNF-REN-001 — Paginación en listados

**Descripción:** Todos los listados de registros implementarán paginación para evitar carga excesiva de datos.

**Criterios de validación:**
- Listados paginan por defecto (25-50 registros por página).
- Navegación clara entre páginas.
- Contador total visible.
- Filtros preservados entre páginas.

**Métrica:** 100% de listados con paginación implementada.

---

#### RNF-REN-002 — Índices de base de datos

**Descripción:** Las consultas frecuentes estarán soportadas por índices adecuados para optimizar tiempos de respuesta.

**Criterios de validación:**
- Índices en claves foráneas.
- Índices en campos de búsqueda frecuente (placas, email, folio, status).
- Índices compuestos para filtros combinados comunes.
- Explain plan revisado para queries críticas.

**Métrica:** Queries principales < 100ms en ambiente de prueba con datos representativos.

---

#### RNF-REN-003 — Prevención de consultas N+1

**Descripción:** El código evitará el problema N+1 en consultas de base de datos mediante eager loading apropiado.

**Criterios de validación:**
- Uso de `with()` en Eloquent para relaciones.
- Herramientas de detección (Laravel Debugbar, Clockwork) en desarrollo.
- CI verifica ausencia de N+1 en pruebas feature.

**Métrica:** 0 consultas N+1 en flujos críticos verificado en profiling.

---

#### RNF-REN-004 — Tiempo de respuesta objetivo

**Descripción:** Las consultas frecuentes tendrán un tiempo de respuesta inferior a dos segundos bajo carga nominal.

**Criterios de validación:**
- Dashboard carga en < 2s.
- Búsquedas responden en < 2s.
- Listados con filtros cargan en < 2s.
- Pruebas de carga validan métricas.

**Métrica:** 95% de requests < 2s en pruebas de rendimiento.

---

#### RNF-REN-005 — Procesos pesados en cola

**Descripción:** Las tareas intensivas o lentas se procesarán asíncronamente mediante colas de jobs.

**Criterios de validación:**
- Generación de documentos PDF en cola.
- Envío de correos en cola.
- Exportaciones grandes en cola.
- Usuario recibe notificación al completar.

**Métrica:** 100% de tareas identificadas como "pesadas" procesadas en background.

---

### 4.3 RNF-DIS — Disponibilidad y continuidad

#### RNF-DIS-001 — Respaldos automáticos de base de datos

**Descripción:** La base de datos se respaldará automáticamente con frecuencia definida.

**Criterios de validación:**
- Backup diario completo automático.
- Retención mínima: 30 días.
- Almacenamiento en ubicación separada del servidor principal.
- Verificación automática de integridad de backup.

**Métrica:** Backup exitoso diario verificado, RPO < 24 horas.

---

#### RNF-DIS-002 — Procedimiento de restauración

**Descripción:** Existirá procedimiento documentado y probado para restaurar la base de datos desde respaldo.

**Criterios de validación:**
- Documentación paso a paso disponible.
- Prueba de restauración trimestral.
- Tiempo objetivo de restauración (RTO) < 4 horas.
- Responsable asignado para ejecución.

**Métrica:** Restauración probada exitosamente en ambiente de staging.

---

#### RNF-DIS-003 — Monitoreo de errores

**Descripción:** El sistema contará con monitoreo de errores y alertas para detección temprana de problemas.

**Criterios de validación:**
- Herramienta de error tracking (Sentry, Bugsnag, similar).
- Alertas configuradas para errores críticos.
- Notificaciones a equipo técnico por canales definidos.
- Dashboard de salud del sistema disponible.

**Métrica:** MTTR (Mean Time To Repair) < 2 horas para incidentes críticos.

---

#### RNF-DIS-004 — Monitoreo de almacenamiento

**Descripción:** El espacio de almacenamiento se monitoreará para prevenir llenado y fallos.

**Criterios de validación:**
- Alertas al 80% de capacidad de disco.
- Alertas al 90% de capacidad crítico.
- Política de rotación de logs implementada.
- Limpieza automática de temporales.

**Métrica:** 0 incidentes por disco lleno en producción.

---

### 4.4 RNF-USA — Usabilidad

#### RNF-USA-001 — Interfaz en español

**Descripción:** Toda la interfaz de usuario estará en idioma español.

**Criterios de validación:**
- Etiquetas, mensajes, botones en español.
- Mensajes de error y validación en español.
- Documentación de usuario en español.
- Excepciones: términos técnicos universalmente aceptados.

**Métrica:** 100% de UI en español verificado en revisión.

---

#### RNF-USA-002 — Diseño adaptable (responsive)

**Descripción:** La interfaz será usable en escritorio, tableta y dispositivos móviles.

**Criterios de validación:**
- Layout se adapta a viewport >= 320px.
- Menús y navegación accesibles en móvil.
- Formularios usables en pantallas pequeñas.
- Tablas con scroll horizontal o vista simplificada en móvil.

**Métrica:** Score > 90 en Lighthouse responsive testing.

---

#### RNF-USA-003 — Navegación consistente

**Descripción:** La navegación seguirá patrones consistentes en toda la aplicación.

**Criterios de validación:**
- Menú principal en ubicación fija.
- Breadcrumbs en páginas anidadas.
- Botones de acción en posiciones predecibles.
- Atajos de teclado documentados y consistentes.

**Métrica:** Usuarios completan tareas clave sin entrenamiento formal.

---

#### RNF-USA-004 — Mensajes de validación claros

**Descripción:** Los mensajes de error y validación serán comprensibles y accionables.

**Criterios de validación:**
- Mensajes en lenguaje de negocio, no técnico.
- Indican qué campo tiene error y por qué.
- Sugieren cómo corregir el error.
- No exponen detalles internos del sistema.

**Métrica:** < 5% de tickets de soporte relacionados con errores confusos.

---

#### RNF-USA-005 — Estados de carga, vacío y error

**Descripción:** La interfaz mostrará estados explícitos para carga, ausencia de datos y errores.

**Criterios de validación:**
- Spinners o skeletons durante carga.
- Mensajes "sin resultados" en listados vacíos.
- Pantallas de error amigables con opción de reintentar.
- Indicadores de progreso en operaciones largas.

**Métrica:** 100% de estados manejados en componentes principales.

---

#### RNF-USA-006 — Accesibilidad básica

**Descripción:** La aplicación seguirá prácticas básicas de accesibilidad web.

**Criterios de validación:**
- Etiquetas semánticas HTML (header, nav, main, footer).
- Atributos `alt` en imágenes.
- Navegación por teclado funcional.
- Contraste de colores suficiente (WCAG AA).
- Labels asociados a inputs.

**Métrica:** Score > 85 en Lighthouse accessibility audit.

---

### 4.5 RNF-MAN — Mantenibilidad

#### RNF-MAN-001 — Convenciones de código

**Descripción:** El código seguirá convenciones documentadas para PHP, Laravel, Vue y TypeScript.

**Criterios de validación:**
- PSR-12 para PHP.
- Guías de estilo Laravel.
- ESLint + Prettier para JavaScript/TypeScript.
- Configuración compartida en repositorio.

**Métrica:** 100% de código pasa linting en CI.

---

#### RNF-MAN-002 — Pruebas automatizadas

**Descripción:** El sistema incluirá pruebas automatizadas para funcionalidad crítica.

**Criterios de validación:**
- Unit tests para lógica de negocio.
- Feature tests para flujos principales.
- Tests de integración para APIs.
- Cobertura mínima 70% en módulos críticos.

**Métrica:** Cobertura reportada en CI, umbral configurado.

---

#### RNF-MAN-003 — Análisis estático

**Descripción:** El código se analizará estáticamente para detectar problemas potenciales.

**Criterios de validación:**
- PHPStan o Psalm configurado nivel 5+.
- Larastan para reglas específicas de Laravel.
- Bloqueo de merge si análisis falla.

**Métrica:** 0 errores de análisis estático en rama principal.

---

#### RNF-MAN-004 — Linting y formato automático

**Descripción:** El código se formateará automáticamente para mantener consistencia.

**Criterios de validación:**
- PHP CS Fixer o Pint configurado.
- Prettier para frontend.
- Hooks de pre-commit opcionales.
- CI verifica formato.

**Métrica:** 100% de código formateado correctamente.

---

#### RNF-MAN-005 — Registro de decisiones arquitectónicas

**Descripción:** Las decisiones arquitectónicas importantes se documentarán como ADRs (Architecture Decision Records).

**Criterios de validación:**
- Plantilla de ADR disponible.
- ADRs versionados en repositorio.
- ADRs incluyen contexto, decisión y consecuencias.
- Referencias cruzadas desde documentación principal.

**Métrica:** ADRs creados para decisiones listadas en plan maestro.

---

#### RNF-MAN-006 — Gestión de dependencias

**Descripción:** Las dependencias se mantendrán actualizadas de forma controlada.

**Criterios de validación:**
- Composer y npm con versiones fijas (lock files).
- Dependabot o similar para alertas de seguridad.
- Proceso de actualización documentado.
- Testing post-actualización obligatorio.

**Métrica:** 0 vulnerabilidades críticas conocidas, updates trimestrales.

---

### 4.6 RNF-DAT — Protección de datos

#### RNF-DAT-001 — Mínimo privilegio

**Descripción:** Los usuarios tendrán acceso mínimo necesario para realizar sus funciones.

**Criterios de validación:**
- Roles definidos con permisos específicos.
- Permisos granulares por acción.
- Revisión periódica de asignaciones.
- Administradores separados de usuarios operativos.

**Métrica:** Auditoría de permisos semestral, 0 accesos innecesarios.

---

#### RNF-DAT-002 — Protección de datos fiscales

**Descripción:** Los datos fiscales sensibles no se expondrán innecesariamente en interfaces o logs.

**Criterios de validación:**
- RFC completo solo visible en vistas autorizadas.
- Datos fiscales no se incluyen en logs estándar.
- Máscara parcial en listados cuando aplique.
- Exportaciones de datos fiscales protegidas.

**Métrica:** 0 exposiciones indebidas en auditoría.

---

#### RNF-DAT-003 — Políticas de retención

**Descripción:** Existirán políticas documentadas para retención y eliminación de datos.

**Criterios de validación:**
- Política define tiempos por tipo de dato.
- Eliminación automática o programada según política.
- Datos históricos críticos preservados según ley.
- Procedimiento de eliminación segura documentado.

**Métrica:** Política documentada y aplicada, cumplimiento verificado anual.

---

#### RNF-DAT-004 — Registro de exportaciones sensibles

**Descripción:** Las exportaciones de datos sensibles registrarán usuario, fecha y alcance.

**Criterios de validación:**
- Exportaciones grandes (>1000 registros) se auditan.
- Exportaciones de datos fiscales se registran.
- Reporte de exportaciones disponible para administradores.

**Métrica:** 100% de exportaciones sensibles registradas.

---

#### RNF-DAT-005 — Cumplimiento de privacidad

**Descripción:** El sistema cumplirá con la Ley Federal de Protección de Datos Personales en Posesión de Particulares de México.

**Criterios de validación:**
- Aviso de privacidad disponible.
- Consentimiento registrado cuando aplique.
- Derechos ARCO (Acceso, Rectificación, Cancelación, Oposición) soportados.
- Encargado de datos personales designado.

**Métrica:** Evaluación de cumplimiento anual, 0 multas o sanciones.

---

### 4.7 RNF-ESC — Escalabilidad

#### RNF-ESC-001 — Arquitectura modular

**Descripción:** El sistema seguirá una arquitectura modular que permita crecimiento orgánico.

**Criterios de validación:**
- Módulos separados por dominio (clientes, unidades, órdenes, etc.).
- Acoplamiento bajo entre módulos.
- Nueva funcionalidad agrega módulos sin modificar existentes.

**Métrica:** Nuevos módulos implementados sin refactor mayor.

---

#### RNF-ESC-002 — Soporte de volumen de datos

**Descripción:** El sistema operará eficientemente con volúmenes esperados de datos.

**Criterios de validación:**
- Soporta 10,000+ clientes.
- Soporta 50,000+ unidades.
- Soporta 500,000+ órdenes de mantenimiento.
- Performance se mantiene dentro de RNF-REN-004.

**Métrica:** Pruebas de carga con datos sintéticos validan métricas.

---

### 4.8 RNF-COM — Compatibilidad

#### RNF-COM-001 — Navegadores soportados

**Descripción:** La aplicación funcionará correctamente en navegadores modernos.

**Criterios de validación:**
- Chrome (últimas 2 versiones).
- Firefox (últimas 2 versiones).
- Safari (últimas 2 versiones).
- Edge (últimas 2 versiones).

**Métrica:** Testing cross-browser en CI o manual antes de releases.

---

#### RNF-COM-002 — Resolución de pantalla

**Descripción:** La aplicación será usable en resoluciones comunes.

**Criterios de validación:**
- Soporta desde 1280x720 hasta 4K.
- Móvil desde 320px de ancho.
- Elementos no se superponen ni cortan.

**Métrica:** Testing en múltiples resoluciones, 0 issues críticos.

---

## 5. Matriz de trazabilidad

| RNF | Objetivos relacionados | Módulo(s) afectados | Tipo de prueba |
|---|---|---|---|
| RNF-SEG-001 | 7 | Todos | Security test |
| RNF-SEG-002 | 7 | Todos | Authorization test |
| RNF-REN-001 | 7, 9 | Todos | Functional test |
| RNF-REN-004 | 9 | Todos | Performance test |
| RNF-USA-001 | 7 | Frontend | UX review |
| RNF-USA-002 | 7 | Frontend | Responsive test |
| RNF-MAN-001 | 8 | Todos | CI/CD |
| RNF-MAN-002 | 9 | Todos | Coverage report |
| RNF-DAT-005 | 8 | Todos | Compliance audit |

---

## 6. Métricas globales de aceptación

| Categoría | Métrica | Umbral mínimo |
|---|---|---|
| Seguridad | Vulnerabilidades críticas | 0 |
| Rendimiento | Requests < 2s | 95% |
| Disponibilidad | Uptime | 99% |
| Usabilidad | Lighthouse accessibility | > 85 |
| Mantenibilidad | Cobertura de tests | > 70% |
| Mantenibilidad | Errores análisis estático | 0 |
| Datos | Incidentes de exposición | 0 |

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
