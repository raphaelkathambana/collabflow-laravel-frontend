/**
 * Alpine.js Bridge for Project Workflow View
 *
 * This bridge connects Livewire (server state) to React (ProjectWorkflow component)
 * for the read-only workflow view on the project detail page.
 *
 * Unlike the create wizard bridge, this is read-only and doesn't save state changes.
 */

import { createRoot } from 'react-dom/client';
import ProjectWorkflow from '../react/components/ProjectWorkflow';

// Use shared React instance
const SharedReact = window.React;
const SharedReactDOM = window.ReactDOM;

if (!SharedReact || !SharedReactDOM) {
    console.error('❌ React/ReactDOM not available for project-workflow-bridge');
    throw new Error('vendor-react.js must load before project-workflow-bridge.js');
}

console.log('✅ Project Workflow Bridge loaded with React', SharedReact.version);

// Store ReactFlowProvider reference
let ReactFlowProvider = null;

// Function to dynamically load ReactFlowProvider
async function loadReactFlowProvider() {
    if (!ReactFlowProvider) {
        const reactFlowModule = await import('@xyflow/react');
        ReactFlowProvider = reactFlowModule.ReactFlowProvider;
    }
    return ReactFlowProvider;
}

export function projectWorkflowBridge() {
    return {
        tasks: [],
        layoutDirection: 'vertical',
        reactRoot: null,
        mounted: false,

        init() {
            console.log('🚀 Project Workflow Bridge initialized');
            console.log('   Tasks:', this.tasks);
            console.log('   Layout:', this.layoutDirection);

            // Mount React component immediately
            this.$nextTick(() => {
                this.mountReact();
            });
        },

        async mountReact() {
            const container = this.$refs.workflowContainer;

            if (!container) {
                console.error('❌ Workflow container ref not found');
                return;
            }

            if (!SharedReact || !SharedReactDOM) {
                console.error('❌ React or ReactDOM not available');
                return;
            }

            console.log('⚛️  Mounting ProjectWorkflow component...');
            console.log('   Container:', container);
            console.log('   Tasks count:', this.tasks?.length || 0);

            const props = {
                tasks: this.tasks || [],
                layoutDirection: this.layoutDirection || 'vertical',
            };

            try {
                // Load ReactFlowProvider
                const Provider = await loadReactFlowProvider();

                // Create root only once
                if (!this.reactRoot) {
                    this.reactRoot = createRoot(container);
                }

                // Render the component wrapped in ReactFlowProvider
                this.reactRoot.render(
                    SharedReact.createElement(
                        Provider,
                        null,
                        SharedReact.createElement(ProjectWorkflow, props)
                    )
                );
                this.mounted = true;

                console.log('✅ ProjectWorkflow component mounted successfully');
            } catch (error) {
                console.error('❌ Error mounting ProjectWorkflow:', error);
            }
        },

        destroy() {
            console.log('🔥 Destroying ProjectWorkflow component');

            try {
                if (this.reactRoot) {
                    const container = this.$refs.workflowContainer;
                    if (container && container.isConnected) {
                        this.reactRoot.unmount();
                    }
                    this.reactRoot = null;
                }
            } catch (error) {
                console.warn('React unmount error (non-critical):', error.message);
            }

            this.mounted = false;
        },
    };
}

// Export for use in Alpine
window.projectWorkflowBridge = projectWorkflowBridge;
