/**
 * ReactDOM Client Shim - Re-exports window.ReactDOM client APIs
 *
 * For react-dom/client imports (createRoot, hydrateRoot).
 */

// Named exports
const {
    createRoot,
    hydrateRoot,
} = window.ReactDOM || {};

export {
    createRoot,
    hydrateRoot,
};
