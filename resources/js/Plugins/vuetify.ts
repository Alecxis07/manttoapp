import 'vuetify/styles';
import { createVuetify, type ThemeDefinition } from 'vuetify';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import { es } from 'vuetify/locale';
import { resolveInitialTheme } from '@/Composables/useDarkMode';

const light: ThemeDefinition = {
    dark: false,
    colors: {
        primary: '#E8A317',
        secondary: '#5A728C',
        accent: '#E8A317',
        error: '#C62828',
        info: '#1565C0',
        success: '#2E7D32',
        warning: '#ED6C02',
        background: '#F6F7F9',
        surface: '#FFFFFF',
        'on-primary': '#1A212B',
        'on-secondary': '#FFFFFF',
        'on-background': '#1A212B',
        'on-surface': '#1A212B',
    },
    variables: {
        'border-color': '#C9D3DE',
        'border-opacity': 1,
        'high-emphasis-opacity': 0.92,
        'medium-emphasis-opacity': 0.72,
        'disabled-opacity': 0.42,
        'idle-opacity': 0.04,
        'hover-opacity': 0.06,
        'focus-opacity': 0.1,
        'selected-opacity': 0.1,
        'activated-opacity': 0.12,
        'pressed-opacity': 0.14,
        'dragged-opacity': 0.1,
    },
};

const dark: ThemeDefinition = {
    dark: true,
    colors: {
        primary: '#E8A317',
        secondary: '#A3B3C5',
        accent: '#E8A317',
        error: '#EF5350',
        info: '#42A5F5',
        success: '#66BB6A',
        warning: '#FFA726',
        background: '#12161C',
        surface: '#1A2029',
        'on-primary': '#1A212B',
        'on-secondary': '#12161C',
        'on-background': '#EEF2F6',
        'on-surface': '#EEF2F6',
    },
    variables: {
        'border-color': '#3A4A5D',
        'border-opacity': 1,
        'high-emphasis-opacity': 0.92,
        'medium-emphasis-opacity': 0.72,
        'disabled-opacity': 0.42,
        'idle-opacity': 0.06,
        'hover-opacity': 0.08,
        'focus-opacity': 0.12,
        'selected-opacity': 0.14,
        'activated-opacity': 0.16,
        'pressed-opacity': 0.18,
        'dragged-opacity': 0.12,
    },
};

export default createVuetify({
    locale: {
        locale: 'es',
        messages: { es },
    },
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
    theme: {
        defaultTheme: resolveInitialTheme(),
        themes: {
            light,
            dark,
        },
    },
    defaults: {
        VCard: {
            rounded: 'xl',
            variant: 'outlined',
            elevation: 0,
        },
        VBtn: {
            rounded: 'lg',
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            hideDetails: 'auto',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            hideDetails: 'auto',
        },
        VAutocomplete: {
            variant: 'outlined',
            density: 'comfortable',
            hideDetails: 'auto',
        },
        VTextarea: {
            variant: 'outlined',
            density: 'comfortable',
            hideDetails: 'auto',
        },
        VDataTable: {
            density: 'comfortable',
        },
        VDataTableServer: {
            density: 'comfortable',
        },
    },
});
