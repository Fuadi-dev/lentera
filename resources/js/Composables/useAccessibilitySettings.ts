import { computed, onMounted, ref, watch } from 'vue';

const STORAGE_KEY = 'accessibility-settings';
const defaultFontScale = 100;
const minimumFontScale = 90;
const maximumFontScale = 130;

interface StoredSettings {
    fontSize?: unknown;
    fontScale?: unknown;
    dyslexiaFont?: unknown;
}

const fontScale = ref(defaultFontScale);
const dyslexiaFont = ref(false);

function applySettings(): void {
    const root = document.documentElement;

    root.style.fontSize = `${fontScale.value}%`;
    root.classList.toggle('dyslexia-font', dyslexiaFont.value);
}

function loadSettings(): void {
    try {
        const savedSettings: StoredSettings = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');

        if (
            typeof savedSettings.fontScale === 'number' &&
            savedSettings.fontScale >= minimumFontScale &&
            savedSettings.fontScale <= maximumFontScale
        ) {
            fontScale.value = savedSettings.fontScale;
        } else if (savedSettings.fontSize === 'small') {
            fontScale.value = 90;
        } else if (savedSettings.fontSize === 'large') {
            fontScale.value = 115;
        }

        if (typeof savedSettings.dyslexiaFont === 'boolean') {
            dyslexiaFont.value = savedSettings.dyslexiaFont;
        }
    } catch {
        // Ignore malformed browser storage and use accessible defaults.
    }

    applySettings();
}

function saveSettings(): void {
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
            fontScale: fontScale.value,
            dyslexiaFont: dyslexiaFont.value,
        })
    );
}

export function useAccessibilitySettings() {
    const isDefault = computed(() => fontScale.value === defaultFontScale && !dyslexiaFont.value);

    onMounted(loadSettings);

    watch([fontScale, dyslexiaFont], () => {
        applySettings();
        saveSettings();
    });

    function resetSettings(): void {
        fontScale.value = defaultFontScale;
        dyslexiaFont.value = false;
    }

    return {
        fontScale,
        dyslexiaFont,
        isDefault,
        resetSettings,
    };
}
