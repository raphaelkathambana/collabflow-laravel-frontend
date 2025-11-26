/**
 * React Vendor Bundle
 *
 * This file bundles React and ReactDOM ONCE and exposes them globally
 * to prevent multiple React instance errors (Error #185).
 *
 * IMPORTANT: This must load BEFORE any other React code (app.js, flowchart.js, etc.)
 *
 * Architecture: Islands Pattern
 * - This creates a SINGLE React instance shared across all "islands"
 * - Alpine.js bridges use window.React to access this instance
 * - React components use window.React instead of importing React
 */

import React from 'react';
import ReactDOM from 'react-dom/client';

// Expose React to window for global access
window.React = React;
window.ReactDOM = ReactDOM;

// CRITICAL: Also expose createRoot directly for easier access
window.ReactDOMClient = ReactDOM;

// Log successful load
console.log('✅ React vendor bundle loaded');
console.log('   React version:', React.version);
console.log('   Available as: window.React, window.ReactDOM, window.ReactDOMClient');
console.log('   Architecture: Islands Pattern (Single React Instance)');

// Verify exports are accessible
if (!window.React || !window.ReactDOM) {
    console.error('❌ CRITICAL: React vendor bundle failed to expose window globals');
    throw new Error('React vendor bundle initialization failed');
}
