import { onMounted, ref, type Ref } from 'vue';
import { useTheme } from 'vuetify';

export type ThemeMode = 'light' | 'dark';

const STORAGE_KEY = 'mantto-theme';

const isDark: Ref<boolean> = ref(false);
let initialized = false;

function readStoredPreference(): ThemeMode | null {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (stored === 'light' || stored === 'dark') {
            return stored;
        }
    } catch {
        // localStorage unavailable
    }

    return null;
}

function systemPrefersDark(): boolean {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

export function resolveInitialTheme(): ThemeMode {
    return readStoredPreference() ?? (systemPrefersDark() ? 'dark' : 'light');
}

function syncDocumentClass(mode: ThemeMode): void {
    document.documentElement.classList.toggle('dark', mode === 'dark');
}

function persistTheme(mode: ThemeMode): void {
    try {
        localStorage.setItem(STORAGE_KEY, mode);
    } catch {
        // localStorage unavailable
    }
}

/**
 * Apply theme to document + storage. Optionally sync Vuetify when a theme instance is provided.
 */
export function applyTheme(mode: ThemeMode, theme?: { global: { name: { value: string } } }): void {
    isDark.value = mode === 'dark';
    syncDocumentClass(mode);
    persistTheme(mode);

    if (theme) {
        theme.global.name.value = mode;
    }
}

export function useDarkMode() {
    const theme = useTheme();

    onMounted(() => {
        if (!initialized) {
            applyTheme(resolveInitialTheme(), theme);
            initialized = true;
        } else {
            isDark.value = document.documentElement.classList.contains('dark');
            theme.global.name.value = isDark.value ? 'dark' : 'light';
        }
    });

    const toggle = (): void => {
        applyTheme(isDark.value ? 'light' : 'dark', theme);
    };

    const setTheme = (mode: ThemeMode): void => {
        applyTheme(mode, theme);
    };

    return {
        isDark,
        toggle,
        setTheme,
    };
}
