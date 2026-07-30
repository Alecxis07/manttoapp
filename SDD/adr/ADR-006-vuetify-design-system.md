# ADR-006 — Design system Vuetify 4

- **Estado:** Aceptado
- **Fecha:** 2026-07-30
- **Contexto:** El MVP arrancó con Jetstream + Tailwind (ADR-001). La UI administrativa creció con wrappers ad hoc (`PrimaryButton`, `Modal`, `SideNavLink`, etc.) y un look inconsistente. Se necesitaba un design system moderno, minimalista, con dark mode, tipado estricto y componentes reutilizables por categoría, sin copiar código propietario de plantillas comerciales.

## Decisión

Adoptar **Vuetify 4** como sistema de UI oficial de ManttoApp:

- Remover **Tailwind CSS** y sus configs/plugins (`tailwindcss`, `@tailwindcss/*`, PostCSS/autoprefixer dedicados).
- Tema **minimalista** light/dark propio (primario ámbar de marca `#E8A317`, superficies neutras, bordes sutiles).
- Iconografía **MDI** (`@mdi/font`).
- Componentes nuevos en **TypeScript SFCs** (`<script setup lang="ts">`).
- Librería interna `resources/js/Components/Ui/` por categorías: Navigation, Dashboard, Modal, Tabs, Forms, Alerts, Data & Display, Selections.
- Shell de aplicación sobre `v-app` (`AppShell` + `AppLayout`), con flash global y menú de Teams/Jetstream conservado.
- Patrones visuales inspirados en Materio (jerarquía, cards, densidad) **sin copiar** assets ni código propietario.

## Consecuencias

- Dependencias frontend vía **pnpm**: `vuetify`, `vite-plugin-vuetify`, `@mdi/font`, Vue `^3.5`; TypeScript pinneado a `~5.9` (compatible con `vue-tsc`; TypeScript 7 rompe el export `./lib/tsc`).
- Tipado Ziggy/Inertia: `resources/js/types/global.d.ts` declara `route` global + `InertiaConfig.sharedPageProps`.
- Migración **atómica**: al quitar Tailwind, todas las páginas deben estar migradas para que el build quede sano.
- Wrappers Jetstream/Tailwind obsoletos eliminados; Jetstream (Auth/Profile/Teams/API tokens) permanece funcional sobre el Ui kit.
- Tests Feature PHPUnit (Inertia component/props) no dependen del HTML Vuetify y deben seguir verdes.
- ADR-001 queda actualizado en espíritu de stack UI: el monolito Inertia se mantiene; el CSS utility-first se sustituye por Vuetify.
