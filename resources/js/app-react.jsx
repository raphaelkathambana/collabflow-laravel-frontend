/**
 * CollabFlow React Application Entry Point
 *
 * This file bootstraps React components for the flowchart/workflow visualization system.
 * Following the "Islands Architecture" pattern - React is used only for interactive flowcharts,
 * while Livewire + Alpine.js manage the rest of the application.
 *
 * NOTE: This file is a LEGACY entry point. The Alpine bridge (alpine-bridge.js)
 * now handles all React mounting. This file is kept for backward compatibility
 * but may be removed in the future.
 */

// Use global React (from vendor-react.js)
const React = window.React;
const ReactDOM = window.ReactDOM;

if (!React || !ReactDOM) {
    console.error('❌ app-react.jsx: React/ReactDOM not available on window');
    throw new Error('vendor-react.js must load before app-react.jsx');
}

import FlowchartContainer from './react/components/flowchart/FlowchartContainer';

/**
 * Initialize React "islands" - mount React components in designated containers
 * This function is called by Alpine.js when needed
 *
 * DEPRECATED: Use Alpine bridge instead (flowchart/alpine-bridge.js)
 */
window.initReactFlowchart = function(containerId, props) {
    const container = document.getElementById(containerId);

    if (!container) {
        console.error(`Container with id "${containerId}" not found`);
        return;
    }

    // Prevent double-mounting
    if (container.dataset.reactMounted === 'true') {
        console.warn(`React component already mounted in "${containerId}"`);
        return;
    }

    const root = ReactDOM.createRoot(container);
    root.render(React.createElement(FlowchartContainer, props));

    container.dataset.reactMounted = 'true';

    console.log('⚛️ React Flowchart mounted (legacy):', containerId);
};

console.log('📦 app-react.jsx loaded (using React', React.version, ')');
