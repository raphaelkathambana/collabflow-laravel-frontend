/**
 * React Shim - Re-exports window.React for all imports
 *
 * This file acts as a replacement for 'react' imports throughout the app.
 * It ensures all code (including @xyflow/react internally) uses the same
 * React instance loaded by vendor-react.js.
 *
 * CRITICAL: This file MUST NOT import the actual react package.
 */

console.log('🔀 React shim loaded - redirecting to window.React');

// Default export - React itself
export default window.React;

// Named exports - commonly used hooks and utilities
const {
    useState,
    useEffect,
    useCallback,
    useMemo,
    useRef,
    useContext,
    useReducer,
    useImperativeHandle,
    useLayoutEffect,
    useDebugValue,
    useId,
    useDeferredValue,
    useTransition,
    useSyncExternalStore,
    useInsertionEffect,
    createContext,
    forwardRef,
    memo,
    lazy,
    Suspense,
    Fragment,
    Component,
    PureComponent,
    createElement,
    cloneElement,
    createRef,
    isValidElement,
    Children,
    StrictMode,
    version,
} = window.React || {};

export {
    useState,
    useEffect,
    useCallback,
    useMemo,
    useRef,
    useContext,
    useReducer,
    useImperativeHandle,
    useLayoutEffect,
    useDebugValue,
    useId,
    useDeferredValue,
    useTransition,
    useSyncExternalStore,
    useInsertionEffect,
    createContext,
    forwardRef,
    memo,
    lazy,
    Suspense,
    Fragment,
    Component,
    PureComponent,
    createElement,
    cloneElement,
    createRef,
    isValidElement,
    Children,
    StrictMode,
    version,
};
