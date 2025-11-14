// Import Alpine.js bridges for React flowchart integration
import { createFlowchartBridge } from './flowchart/alpine-bridge.js';
import { projectWorkflowBridge } from './flowchart/project-workflow-bridge.js';

// Register Alpine components
// Handle both cases: Alpine already loaded, or waiting for alpine:init
function registerFlowchartBridge() {
    if (window.Alpine) {
        window.Alpine.data('flowchartBridge', createFlowchartBridge);
        window.Alpine.data('projectWorkflowBridge', projectWorkflowBridge);
        console.log('✅ Alpine flowchartBridge and projectWorkflowBridge components registered');
    }
}

// Try to register immediately if Alpine is already loaded
if (window.Alpine) {
    registerFlowchartBridge();
} else {
    // Otherwise wait for alpine:init event
    document.addEventListener('alpine:init', registerFlowchartBridge);
}

// Flux handles theme management automatically via @fluxAppearance directive
// No custom theme code needed - Flux uses $flux.dark and $flux.appearance

// Command Palette Keyboard Shortcut (Cmd+K / Ctrl+K)
document.addEventListener('keydown', function(e) {
    // Check for Cmd+K (Mac) or Ctrl+K (Windows/Linux)
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault(); // Prevent browser default behavior

        // Dispatch event to open command palette
        window.dispatchEvent(new CustomEvent('open-command-palette'));
    }
});
