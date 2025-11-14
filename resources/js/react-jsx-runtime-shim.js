/**
 * React JSX Runtime Shim - Re-exports window.React JSX functions
 *
 * For react/jsx-runtime imports (used by automatic JSX transform).
 */

// Named exports for JSX
const {
    jsx,
    jsxs,
    Fragment,
} = window.React || {};

export {
    jsx,
    jsxs,
    Fragment,
};
