/**
 * Theme-aware styles for flowchart components
 * Uses CSS variables that adapt to Flux dark/light mode
 */

export const themeStyles = {
    // Backgrounds
    bg: {
        primary: 'var(--color-background-50)',
        card: 'var(--color-background-100)',
        hover: 'var(--color-background-200)',
        border: 'var(--color-background-300)',
    },

    // Text colors
    text: {
        primary: 'var(--color-text-900)',
        secondary: 'var(--color-text-700)',
        muted: 'var(--color-text-600)',
        disabled: 'var(--color-text-400)',
    },

    // Borders
    border: {
        default: 'var(--color-background-300)',
        focus: 'var(--color-accent-500)',
    },

    // Brand colors (these stay consistent)
    brand: {
        glaucous: 'var(--color-glaucous)',
        teaGreen: 'var(--color-tea-green)',
        orangePeel: 'var(--color-orange-peel)',
        bittersweet: 'var(--color-bittersweet)',
    },

    // Component-specific styles
    panel: {
        backgroundColor: 'var(--color-background-50)',
        borderColor: 'var(--color-background-300)',
        color: 'var(--color-text-900)',
    },

    button: {
        backgroundColor: 'var(--color-background-100)',
        hoverBackgroundColor: 'var(--color-background-200)',
        color: 'var(--color-text-700)',
        borderColor: 'var(--color-background-300)',
    },

    input: {
        backgroundColor: 'var(--color-background-50)',
        borderColor: 'var(--color-background-300)',
        color: 'var(--color-text-900)',
        placeholderColor: 'var(--color-text-500)',
    },
};

/**
 * Helper to create inline style objects
 */
export const createStyle = (styleObj) => {
    const result = {};
    Object.entries(styleObj).forEach(([key, value]) => {
        // Convert camelCase to kebab-case for CSS properties
        const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
        result[cssKey] = value;
    });
    return result;
};

/**
 * Common component styles as inline style objects
 */
export const componentStyles = {
    card: {
        backgroundColor: themeStyles.bg.card,
        borderColor: themeStyles.border.default,
        color: themeStyles.text.primary,
    },

    toolbar: {
        backgroundColor: themeStyles.bg.card,
        borderColor: themeStyles.border.default,
    },

    button: {
        backgroundColor: themeStyles.button.backgroundColor,
        color: themeStyles.button.color,
        borderColor: themeStyles.button.borderColor,
    },

    buttonHover: {
        backgroundColor: themeStyles.button.hoverBackgroundColor,
    },

    input: {
        backgroundColor: themeStyles.input.backgroundColor,
        borderColor: themeStyles.input.borderColor,
        color: themeStyles.input.color,
    },
};
