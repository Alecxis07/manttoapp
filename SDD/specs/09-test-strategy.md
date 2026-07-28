# 09 - Estrategia de Pruebas

**Código del Documento:** SDD-TEST-STR-001  
**Versión:** 1.0  
**Fecha:** 2026-01-15  
**Estado:** En desarrollo  

---

## Control de Cambios

| Versión | Fecha       | Autor           | Descripción del Cambio                           | Aprobado Por |
|---------|-------------|-----------------|--------------------------------------------------|--------------|
| 1.0     | 2026-01-15  | Sistema SDD     | Creación inicial de estrategia de pruebas        | Pendiente    |

---

## 1. Introducción

### 1.1 Propósito
Este documento define la estrategia integral de pruebas para el sistema SSD, estableciendo los enfoques, tipos, herramientas y criterios de calidad que se aplicarán durante todo el ciclo de desarrollo.

### 1.2 Alcance
Cubre todas las actividades de pruebas desde desarrollo hasta producción, incluyendo pruebas unitarias, de integración, de sistema, de aceptación, de rendimiento y de seguridad.

### 1.3 Referencias
- [04-functional-requirements.md](04-functional-requirements.md) - Requerimientos Funcionales
- [08-acceptance-criteria.md](08-acceptance-criteria.md) - Criterios de Aceptación
- [07-domain-model.md](07-domain-model.md) - Modelo de Dominio

---

## 2. Enfoque de Pruebas

### 2.1 Pirámide de Pruebas

```
                    ┌─────────────┐
                    │   E2E/UI    │  10%
                   ╱│   (Cypress) │╲
                  ╱ └─────────────┘ ╲
                 ╱───────────────────╲
                ╱    Integración      ╲
               ╱     (Jest/Supertest)  ╲  30%
              ╱─────────────────────────╲
             ╱                           ╲
            ╱         Unitarias           ╲
           ╱          (Jest)               ╲  60%
          ╱─────────────────────────────────╲
```

### 2.2 Niveles de Prueba

| Nivel | Responsable | Automatización | Frecuencia | Cobertura Mínima |
|-------|-------------|----------------|------------|------------------|
| Unitarias | Desarrolladores | 100% | Con cada commit | 80% código |
| Integración | Desarrolladores + QA | 90% | Nocturno (CI) | APIs críticas |
| Sistema | QA | 70% | Pre-release | Flujos completos |
| Aceptación | QA + PO | 50% | Pre-release | Criterios críticos |
| Rendimiento | QA + DevOps | 80% | Mensual | Escenarios clave |
| Seguridad | Security Team | 60% | Trimestral | OWASP Top 10 |

---

## 3. Tipos de Pruebas

### 3.1 Pruebas Unitarias

**Objetivo:** Validar el funcionamiento correcto de unidades individuales de código (funciones, métodos, clases).

**Herramientas:**
- Jest (framework principal)
- Testing Library (para componentes React)
- Mocks y stubs para dependencias externas

**Alcance:**
- Funciones puras y utilidades
- Servicios y lógica de negocio
- Componentes UI aislados
- Validadores y transformadores

**Ejemplo de Estructura:**

```typescript
// __tests__/services/orden.service.test.ts
describe('OrdenService', () => {
  describe('crearOrden', () => {
    it('debe crear una orden con datos válidos', async () => {
      // Arrange
      const datosOrden = { ... };
      
      // Act
      const resultado = await ordenService.crearOrden(datosOrden);
      
      // Assert
      expect(resultado.estado).toBe('BORRADOR');
      expect(resultado.numero).toMatch(/ORD-\d{4}-\d{6}/);
    });
    
    it('debe lanzar error si no hay ítems', async () => {
      // Arrange
      const datosOrden = { clienteId: '123', items: [] };
      
      // Act & Assert
      await expect(ordenService.crearOrden(datosOrden))
        .rejects.toThrow('La orden debe tener al menos un ítem');
    });
  });
});
```

**Criterios de Aceptación:**
- [ ] Cobertura mínima 80% de líneas
- [ ] Cobertura mínima 70% de ramas
- [ ] Todas las pruebas deben pasar en CI
- [ ] Tiempo máximo de ejecución: 5 minutos

---

### 3.2 Pruebas de Integración

**Objetivo:** Validar la interacción correcta entre componentes, servicios y sistemas externos.

**Herramientas:**
- Jest + Supertest (APIs)
- Testcontainers (bases de datos, Redis)
- WireMock (servicios externos mockeados)

**Alcance:**
- Endpoints REST completos
- Interacción con base de datos
- Integración con servicios externos (SUNAT, email, storage)
- Colas de mensajes y eventos

**Ejemplo de Estructura:**

```typescript
// __tests__/integration/ordenes.api.test.ts
describe('API Ordenes - Integración', () => {
  let app: Express;
  let db: Database;
  
  beforeAll(async () => {
    db = await TestContainers.startPostgres();
    app = await createApp(db);
  });
  
  afterAll(async () => {
    await db.stop();
  });
  
  describe('POST /api/ordenes', () => {
    it('debe crear orden y retornar 201', async () => {
      const payload = {
        clienteId: uuid(),
        unidadId: uuid(),
        categoriaId: uuid(),
        items: [{ concepto: 'Servicio', cantidad: 1, precio: 100 }]
      };
      
      const response = await request(app)
        .post('/api/ordenes')
        .send(payload)
        .set('Authorization', `Bearer ${token}`);
      
      expect(response.status).toBe(201);
      expect(response.body.numero).toBeDefined();
      expect(response.body.total).toBe(118); // 100 + 18% IGV
    });
    
    it('debe retornar 400 si datos son inválidos', async () => {
      const response = await request(app)
        .post('/api/ordenes')
        .send({ clienteId: 'invalido' });
      
      expect(response.status).toBe(400);
      expect(response.body.errors).toBeDefined();
    });
  });
});
```

**Criterios de Aceptación:**
- [ ] APIs críticas cubiertas 100%
- [ ] Escenarios happy path y error cubiertos
- [ ] Base de datos en estado conocido antes/después de cada test
- [ ] Tiempo máximo de ejecución: 15 minutos

---

### 3.3 Pruebas de Sistema (End-to-End)

**Objetivo:** Validar flujos completos del sistema desde la perspectiva del usuario.

**Herramientas:**
- Cypress (principal)
- Playwright (alternativa para cross-browser)

**Alcance:**
- Flujos críticos de negocio
- Navegación completa de la aplicación
- Integración frontend-backend
- Experiencia de usuario completa

**Escenarios Críticos a Cubrir:**

| ID | Escenario | Módulo | Prioridad |
|----|-----------|--------|-----------|
| E2E-001 | Login exitoso y acceso al dashboard | SEG | Crítica |
| E2E-002 | Registro completo de cliente | CLI | Crítica |
| E2E-003 | Creación de orden de servicio completa | ORD | Crítica |
| E2E-004 | Generación y aprobación de cotización | COT | Alta |
| E2E-005 | Conversión de cotización a factura | FAC | Crítica |
| E2E-006 | Emisión de factura electrónica | FAC | Crítica |
| E2E-007 | Búsqueda y filtrado de órdenes | ORD | Alta |
| E2E-008 | Subida y gestión de archivos | ARC | Media |
| E2E-009 | Consulta de historial de unidad | HIS | Media |
| E2E-010 | Gestión de usuarios y roles | SEG | Alta |

**Ejemplo de Test E2E:**

```typescript
// cypress/e2e/ordenes/creacion-orden.cy.ts
describe('Creación de Orden de Servicio', () => {
  beforeEach(() => {
    cy.login('admin@ssd.com', 'Password123!');
    cy.createCliente('Cliente Test');
    cy.createUnidad('Vehículo Test');
  });
  
  it('debe crear una orden completa exitosamente', () => {
    // Navegar a módulo de órdenes
    cy.visit('/ordenes');
    cy.contains('Nueva Orden').click();
    
    // Completar formulario
    cy.selectCliente('Cliente Test');
    cy.selectUnidad('Vehículo Test');
    cy.selectCategoria('Mantenimiento Preventivo');
    cy.selectPrioridad('MEDIA');
    
    // Agregar ítems
    cy.agregarItem('Cambio de aceite', 1, 150);
    cy.agregarItem('Filtro de aire', 2, 45);
    
    // Verificar cálculos automáticos
    cy.get('[data-testid="subtotal"]').should('contain', '240.00');
    cy.get('[data-testid="igv"]').should('contain', '43.20');
    cy.get('[data-testid="total"]').should('contain', '283.20');
    
    // Guardar orden
    cy.contains('Guardar').click();
    
    // Verificar creación exitosa
    cy.url().should('match', /\/ordenes\/.*$/);
    cy.get('[data-testid="numero-orden"]').should('exist');
    cy.contains('Orden creada exitosamente').should('be.visible');
    
    // Verificar en listado
    cy.visit('/ordenes');
    cy.filtrarPorCliente('Cliente Test');
    cy.get('tbody tr').first().should('contain', 'Cliente Test');
  });
  
  it('debe mostrar errores de validación', () => {
    cy.visit('/ordenes/nueva');
    cy.contains('Guardar').click();
    
    cy.get('.error-message').should('contain', 'Cliente es requerido');
    cy.get('.error-message').should('contain', 'Debe agregar al menos un ítem');
  });
});
```

**Criterios de Aceptación:**
- [ ] 10 escenarios críticos cubiertos
- [ ] Ejecución en Chrome, Firefox, Safari
- [ ] Screenshots automáticos en fallos
- [ ] Videos de ejecución disponibles
- [ ] Tiempo máximo por escenario: 3 minutos

---

### 3.4 Pruebas de Aceptación (BDD)

**Objetivo:** Validar que el sistema cumple con los criterios de aceptación definidos por el negocio.

**Herramientas:**
- Cucumber (Gherkin)
- Cypress Cucumber Plugin

**Alcance:**
- Todos los criterios de aceptación críticos y altos
- Validación con stakeholders
- Documentación ejecutable

**Ejemplo de Feature File:**

```gherkin
# features/emision-factura.feature
Feature: Emisión de Factura Electrónica
  Como administrador del sistema
  Quiero emitir facturas electrónicas válidas SUNAT
  Para cumplir con obligaciones tributarias

  Scenario: Emisión exitosa con todos los requisitos
    Given una cotización APROBADA con total 1000.00
    And un cliente con RUC válido "20123456789"
    And configuración SUNAT válida en el sistema
    When convierto la cotización a factura
    Then el sistema genera XML UBL 2.1 válido
    And el sistema firma digitalmente el documento
    And SUNAT retorna CDR con código "0"
    And el estado cambia a "EMITIDA"
    And se envía email al cliente con PDF y XML
    And se registra evento de auditoría

  Scenario: Emisión fallida por RUC inválido
    Given una cotización APROBADA
    And un cliente sin RUC válido
    When intento convertir a factura
    Then el sistema muestra error "RUC inválido"
    And NO se genera la factura
    And el código de error es "FACTURA_RUC_INVALIDO"

  Scenario: Emisión fallida por conexión SUNAT caída
    Given una cotización APROBADA válida
    And el servicio SUNAT no está disponible
    When intento emitir factura
    Then el sistema reintenta 3 veces
    And muestra mensaje "Servicio SUNAT no disponible"
    And la factura queda en estado "PENDIENTE_EMISION"
    And se programa reintentro automático
```

**Criterios de Aceptación:**
- [ ] Features escritos en lenguaje de negocio
- [ ] Revisados por Product Owner
- [ ] Automatizados y ejecutables
- [ ] Reportes legibles para stakeholders

---

### 3.5 Pruebas de Rendimiento

**Objetivo:** Validar que el sistema cumple con los requerimientos no funcionales de rendimiento.

**Herramientas:**
- k6 (principal)
- JMeter (alternativa)

**Escenarios de Prueba:**

| Escenario | Usuarios Concurrentes | Duración | Métrica Objetivo |
|-----------|----------------------|----------|------------------|
| Login | 100 | 5 min | < 2s respuesta |
| Búsqueda de órdenes | 50 | 10 min | < 3s respuesta |
| Creación de orden | 30 | 10 min | < 2s respuesta |
| Emisión de factura | 20 | 15 min | < 5s respuesta |
| Listado de clientes (paginado) | 100 | 10 min | < 2s respuesta |
| Carga masiva (stress) | 500 | 30 min | Sistema estable |

**Script de Ejemplo (k6):**

```javascript
// tests/performance/ordenes-load.js
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// Métricas personalizadas
const successRate = new Rate('check_succeeded');
const loginDuration = new Trend('login_duration');

export const options = {
  stages: [
    { duration: '2m', target: 50 },   // Ramp up a 50 usuarios
    { duration: '5m', target: 50 },   // Mantener 50 usuarios
    { duration: '2m', target: 100 },  // Ramp up a 100 usuarios
    { duration: '5m', target: 100 },  // Mantener 100 usuarios
    { duration: '2m', target: 0 },    // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000'], // 95% de requests < 3s
    successRate: ['rate>0.99'],        // 99% éxito
    loginDuration: ['avg<2000'],       // Login promedio < 2s
  },
};

export default function () {
  // Login
  const loginStart = Date.now();
  const loginRes = http.post('/api/auth/login', {
    email: `usuario${__VU}@test.com`,
    password: 'Password123!',
  });
  
  loginDuration.add(Date.now() - loginStart);
  check(loginRes, {
    'login status es 200': (r) => r.status === 200,
  });
  successRate.add(check(loginRes, { 'login ok': (r) => r.status === 200 }));
  
  const token = loginRes.json('token');
  
  // Crear orden
  const headers = { Authorization: `Bearer ${token}` };
  const ordenRes = http.post('/api/ordenes', JSON.stringify({
    clienteId: 'uuid-valido',
    unidadId: 'uuid-valido',
    items: [{ concepto: 'Test', cantidad: 1, precio: 100 }],
  }), { headers });
  
  check(ordenRes, {
    'crear orden status es 201': (r) => r.status === 201,
  });
  successRate.add(check(ordenRes, { 'orden ok': (r) => r.status === 201 }));
  
  sleep(1);
}
```

**Criterios de Aceptación:**
- [ ] 95% de requests bajo 3 segundos
- [ ] 99% tasa de éxito
- [ ] Sin memory leaks después de 1 hora
- [ ] CPU promedio < 70% bajo carga
- [ ] Memoria promedio < 80% bajo carga

---

### 3.6 Pruebas de Seguridad

**Objetivo:** Identificar vulnerabilidades y validar controles de seguridad.

**Herramientas:**
- OWASP ZAP (escaneo automatizado)
- Snyk (dependencias)
- SonarQube (análisis estático)
- Burp Suite (pruebas manuales)

**Alcance:**
- OWASP Top 10
- Autenticación y autorización
- Inyección SQL
- XSS (Cross-Site Scripting)
- CSRF (Cross-Site Request Forgery)
- Manejo de sesiones
- Encriptación de datos sensibles

**Checklist de Seguridad:**

| Categoría | Prueba | Frecuencia |
|-----------|--------|------------|
| Autenticación | Fuerza bruta, credential stuffing | Trimestral |
| Autorización | Escalada de privilegios, IDOR | Trimestral |
| Input Validation | SQL injection, XSS, command injection | Trimestral |
| Sesiones | Session fixation, hijacking | Trimestral |
| API Security | Rate limiting, mass assignment | Trimestral |
| Datos Sensibles | Encriptación en tránsito/reposo | Trimestral |
| Dependencias | Vulnerabilidades conocidas | Semanal (CI) |
| Headers | Security headers (CSP, HSTS, etc.) | Trimestral |

**Criterios de Aceptación:**
- [ ] 0 vulnerabilidades críticas
- [ ] 0 vulnerabilidades altas
- [ ] Máximo 5 vulnerabilidades medias (con plan de remediación)
- [ ] Todas las dependencias actualizadas
- [ ] Security headers implementados

---

### 3.7 Pruebas de Regresión

**Objetivo:** Asegurar que cambios nuevos no rompan funcionalidad existente.

**Estrategia:**
- Suite de regresión automatizada ejecutada en CI/CD
- Selección inteligente de tests basada en cambios
- Ejecución completa pre-release

**Cobertura:**
- 100% de funcionalidades críticas
- 80% de funcionalidades importantes
- 50% de funcionalidades menores

---

### 3.8 Pruebas Exploratorias

**Objetivo:** Descubrir defectos no cubiertos por pruebas estructuradas.

**Enfoque:**
- Sessions time-boxed (60-90 minutos)
- Charters definidos por área de riesgo
- Documentación de hallazgos en tiempo real

**Frecuencia:**
- Sprint review (cada 2 semanas)
- Pre-release mayor

---

## 4. Ambiente de Pruebas

### 4.1 Ambientes Disponibles

| Ambiente | Propósito | Datos | Acceso |
|----------|-----------|-------|--------|
| Local | Desarrollo individual | Seed data básico | Desarrolladores |
| Dev | Integración continua | Datos ficticios | Equipo técnico |
| QA | Pruebas sistemáticas | Datos anonimizados | QA + Stakeholders |
| Staging | Pre-producción | Mirror de producción | PO + QA |
| Prod | Producción | Datos reales | Usuarios finales |

### 4.2 Requisitos de Ambiente QA

```yaml
# docker-compose.test.yml
version: '3.8'
services:
  postgres:
    image: postgres:15-alpine
    environment:
      POSTGRES_DB: ssd_test
      POSTGRES_USER: test_user
      POSTGRES_PASSWORD: test_pass
  
  redis:
    image: redis:7-alpine
  
  api:
    build: ./api
    environment:
      NODE_ENV: test
      DATABASE_URL: postgres://test_user:test_pass@postgres:5432/ssd_test
      REDIS_URL: redis://redis:6379
  
  web:
    build: ./web
    environment:
      REACT_APP_API_URL: http://api:3000
  
  cypress:
    image: cypress/included:13.0
    depends_on:
      - api
      - web
```

---

## 5. Datos de Prueba

### 5.1 Estrategia de Datos

- **Desarrollo:** Seed data mínimo (10 clientes, 20 unidades, 50 órdenes)
- **QA:** Dataset representativo (100 clientes, 200 unidades, 500 órdenes)
- **Performance:** Dataset grande (10K clientes, 50K órdenes)
- **Staging:** Anonimizado de producción

### 5.2 Fábricas de Datos (Factories)

```typescript
// tests/factories/cliente.factory.ts
export const clienteFactory = {
  natural: () => ({
    tipo: 'NATURAL',
    nombre: faker.person.fullName(),
    documentoTipo: 'DNI',
    documentoNumero: faker.string.numeric(8),
    email: faker.internet.email(),
    telefono: faker.phone.number(),
    estado: 'ACTIVO',
  }),
  
  juridico: () => ({
    tipo: 'JURIDICO',
    nombre: faker.company.name(),
    documentoTipo: 'RUC',
    documentoNumero: faker.helpers.replaceSymbolWithNumber('20#########'),
    email: faker.internet.companyEmail(),
    telefono: faker.phone.number(),
    estado: 'ACTIVO',
  }),
};
```

---

## 6. Criterios de Calidad

### 6.1 Métricas de Cobertura

| Tipo de Prueba | Cobertura Mínima | Herramienta de Medición |
|----------------|------------------|------------------------|
| Unitarias | 80% líneas, 70% ramas | Jest + Istanbul |
| Integración | 100% endpoints críticos | Swagger + Custom |
| E2E | 100% flujos críticos | Cypress Dashboard |
| Seguridad | OWASP Top 10 completo | OWASP ZAP |

### 6.2 Definición de Terminado (DoD) para Pruebas

Para que una historia de usuario se considere completada:

- [ ] Pruebas unitarias escritas y pasando (>80% cobertura)
- [ ] Pruebas de integración escritas y pasando
- [ ] Criterios de aceptación verificados
- [ ] Pruebas E2E actualizadas si aplica
- [ ] Sin regressions en suite existente
- [ ] Código revisado por pares
- [ ] Análisis estático sin issues críticos
- [ ] Documentación actualizada

---

## 7. Pipeline de CI/CD

### 7.1 Flujo de Integración Continua

```yaml
# .github/workflows/ci.yml
name: CI Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [develop]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: npm ci
      - run: npm run lint

  test-unit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: npm ci
      - run: npm run test:unit -- --coverage
      - uses: codecov/codecov-action@v3

  test-integration:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_PASSWORD: postgres
    steps:
      - uses: actions/checkout@v3
      - run: npm ci
      - run: npm run test:integration

  test-e2e:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: cypress-io/github-action@v6
        with:
          start: npm run start
          wait-on: 'http://localhost:3000'
      - uses: actions/upload-artifact@v3
        if: failure()
        with:
          name: cypress-screenshots
          path: cypress/screenshots

  security-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: npm audit --audit-level=high
      - uses: snyk/actions/node@master

  deploy-staging:
    needs: [lint, test-unit, test-integration, test-e2e, security-scan]
    if: github.ref == 'refs/heads/develop'
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Staging
        run: ./deploy.sh staging
```

---

## 8. Reportes y Métricas

### 8.1 Dashboards

| Dashboard | Herramienta | Audiencia | Frecuencia |
|-----------|-------------|-----------|------------|
| Cobertura de Pruebas | Codecov | Equipo Técnico | Diario |
| Resultados CI/CD | GitHub Actions | Equipo Técnico | Por commit |
| Tests E2E | Cypress Dashboard | QA + PO | Diario |
| Vulnerabilidades | Snyk + SonarQube | Security + Tech Lead | Semanal |
| Rendimiento | k6 Cloud | DevOps + Arquitecto | Semanal |

### 8.2 Métricas Clave (KPIs)

| KPI | Objetivo | Frecuencia de Medición |
|-----|----------|------------------------|
| % Tests Passing | > 98% | Por commit |
| Coverage | > 80% | Por sprint |
| Build Time | < 15 min | Por commit |
| Defect Density | < 1 defecto/KLOC | Por release |
| Mean Time to Detection | < 24 horas | Continuo |
| Escape Defects | < 5% a producción | Por release |

---

## 9. Gestión de Defectos

### 9.1 Severidad de Defectos

| Severidad | Descripción | Tiempo Respuesta | Ejemplo |
|-----------|-------------|------------------|---------|
| Crítica | Sistema inutilizable | 4 horas | No se puede autenticar |
| Alta | Funcionalidad principal rota | 24 horas | No se puede crear orden |
| Media | Funcionalidad secundaria afectada | 1 semana | Búsqueda lenta |
| Baja | Cosmético, mejora menor | Backlog | Color incorrecto |

### 9.2 Flujo de Gestión

```
Reportado → Triaje → Asignado → En Progreso → En Revisión → Verificado → Cerrado
                ↓
            Rechazado (No reproducible, No es bug, Duplicado)
```

---

## 10. Herramientas

### 10.1 Stack de Pruebas

| Categoría | Herramienta | Licencia |
|-----------|-------------|----------|
| Framework Unitarias | Jest | MIT |
| E2E Testing | Cypress | MIT |
| Performance Testing | k6 | AGPL |
| Security Scanning | OWASP ZAP | Apache 2.0 |
| Dependency Check | Snyk | Freemium |
| Code Quality | SonarQube | LGPL |
| Coverage | Istanbul | BSD |
| API Testing | Supertest | MIT |
| Mocking | WireMock | Apache 2.0 |
| Test Data | Faker.js | MIT |
| CI/CD | GitHub Actions | Freemium |
| Test Management | Xray/TestRail | Comercial |

---

## 11. Apéndices

### 11.1 Plantilla de Reporte de Defecto

```markdown
## Título del Defecto

**ID:** DEF-YYYY-NNNN  
**Severidad:** [Crítica|Alta|Media|Baja]  
**Prioridad:** [Crítica|Alta|Media|Baja]  
**Módulo:** [Nombre del módulo]  
**Ambiente:** [Dev|QA|Staging|Prod]  

### Descripción
[Descripción clara y concisa del problema]

### Pasos para Reproducir
1. [Paso 1]
2. [Paso 2]
3. [Paso 3]

### Resultado Esperado
[Lo que debería ocurrir]

### Resultado Actual
[Lo que ocurre actualmente]

### Evidencia
- [ ] Screenshots
- [ ] Videos
- [ ] Logs
- [ ] Capturas de red

### Información Técnica
- **Navegador:** [Chrome 120, Firefox 121, etc.]
- **SO:** [Windows 11, macOS Sonoma, Ubuntu 22.04]
- **Versión:** [v1.2.3]
- **URL:** [https://...]

### Criterios de Aceptación Relacionados
- [CA-XXX-YYY](../specs/08-acceptance-criteria.md)

### Notas Adicionales
[Cualquier información adicional relevante]
```

---

## 12. Aprobaciones

| Rol               | Nombre | Firma | Fecha | Estado     |
|-------------------|--------|-------|-------|------------|
| Líder QA          |        |       |       | Pendiente  |
| Arquitecto SW     |        |       |       | Pendiente  |
| DevOps Lead       |        |       |       | Pendiente  |
| Product Owner     |        |       |       | Pendiente  |

---

**Fin del Documento**
