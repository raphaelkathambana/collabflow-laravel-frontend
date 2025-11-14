// Hybrid pattern: Import for Vite plugin, then override with window.React
import React from 'react';
import FlowchartContainer from './flowchart/FlowchartContainer';

// Override with window.React (the shared instance from vendor-react.js)
const SharedReact = window.React || React;

// Verify React is available
if (!SharedReact) {
    console.error('❌ React not found on window in ProjectWorkflow');
    throw new Error('React not available - vendor-react.js must load first');
}

/**
 * ProjectWorkflow Component
 *
 * Read-only flowchart display for viewing project workflow on the project detail page.
 * This is a wrapper around FlowchartContainer configured for read-only viewing.
 *
 * @param {Object} props
 * @param {Array} props.tasks - Array of task objects
 * @param {string} props.layoutDirection - 'horizontal' or 'vertical'
 */
const ProjectWorkflow = ({ tasks = [], layoutDirection = 'vertical' }) => {
    return (
        <FlowchartContainer
            tasks={tasks}
            onTasksChange={null}  // No changes allowed in read-only mode
            readOnly={true}
            layoutDirection={layoutDirection}
        />
    );
};

export default ProjectWorkflow;
