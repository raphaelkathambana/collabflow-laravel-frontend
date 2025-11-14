/**
 * ReactDOM Shim - Re-exports window.ReactDOM for all imports
 *
 * This ensures react-dom imports also use the global instance.
 */

// Default export
export default window.ReactDOM;

// Named exports
const {
    createPortal,
    flushSync,
    unstable_batchedUpdates,
    version,
} = window.ReactDOM || {};

export {
    createPortal,
    flushSync,
    unstable_batchedUpdates,
    version,
};
