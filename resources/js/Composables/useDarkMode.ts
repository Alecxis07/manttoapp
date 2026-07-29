import { onMounted, ref, type Ref } from 'vue';

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

export function applyTheme(mode: ThemeMode): void {
    const dark = mode === 'dark';
    isDark.value = dark;
    document.documentElement.classList.toggle('dark', dark);

    try {
        localStorage.setItem(STORAGE_KEY, mode);
    } catch {
        // localStorage unavailable
    }
}

export function useDarkMode() {
    onMounted(() => {
        if (! initialized) {
            applyTheme(resolveInitialTheme());
            initialized = true;
        } else {
            isDark.value = document.documentElement.classList.contains('dark');
        }
    });

    const toggle = (): void => {
        applyTheme(isDark.value ? 'light' : 'dark');
    };

    const setTheme = (mode: ThemeMode): void => {
        applyTheme(mode);
    };

    return {
        isDark,
        toggle,
        setTheme,
    };
}
