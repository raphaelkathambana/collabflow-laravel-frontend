/**
 * Alpine.js Bridge Component for React Flowchart
 *
 * This is the critical "bridge layer" in the Islands Architecture pattern:
 * Livewire (server state) ↔ Alpine.js (bridge) ↔ React (UI)
 *
 * Purpose:
 * - Mount React flowchart components in designated containers
 * - Handle two-way data flow between Livewire and React
 * - Debounce position updates to avoid excessive server calls
 * - Re-render React when Livewire state changes
 */

// Use window versions ONLY (shared instances from vendor-react.js)
// CRITICAL: DO NOT import React/ReactDOM directly - this would create a second React instance!
const SharedReact = window.React;
const SharedReactDOM = window.ReactDOM;

// Verify React is available
if (!SharedReact || !SharedReactDOM) {
    console.error('❌ React/ReactDOM not found on window');
    console.error('   Ensure vendor-react.js loads before Alpine bridge');
    throw new Error('React must be loaded before Alpine bridge');
}

console.log('🌉 Alpine bridge initialized with React', SharedReact.version);

// CRITICAL: Use DYNAMIC import to prevent bundling React Flow in app.js
// This creates a separate chunk that's only loaded when the flowchart is actually used
let FlowchartContainer = null;
let ReactFlowProvider = null;

async function loadFlowchartComponent() {
    if (!FlowchartContainer) {
        const module = await import('../react/components/flowchart/FlowchartContainer');
        FlowchartContainer = module.default;

        // Also import ReactFlowProvider
        const reactFlowModule = await import('@xyflow/react');
        ReactFlowProvider = reactFlowModule.ReactFlowProvider;
    }
    return { FlowchartContainer, ReactFlowProvider };
}

export function createFlowchartBridge() {
    return {
        // Component state
        mounted: false,
        reactRoot: null,
        containerId: '',
        saveTimeout: null,
        loading: false,

        // Reactive data properties (will be set via x-init in Blade)
        tasks: [],
        readOnly: false,
        layoutDirection: 'vertical',
        selectedTaskId: null, // For bidirectional sync

        // Stable callback references (created once, reused for all renders)
        _tasksChangeCallback: null,
        _nodeClickCallback: null,

        /**
         * Initialize the Alpine component
         * Called automatically when x-data is processed
         */
        init() {
            this.containerId = this.$el.id || `flowchart-${Date.now()}`;

            // Create stable callback reference for workflow state changes
            this._tasksChangeCallback = (updatedWorkflowState) => this.handleWorkflowUpdate(updatedWorkflowState);

            // Create stable callback reference for node clicks
            this._nodeClickCallback = (taskId) => this.handleNodeClick(taskId);

            // Mount React on next tick to ensure DOM is ready
            this.$nextTick(() => {
                this.mountReact();
            });

            // Watch for tasks changes and re-render React
            this.$watch('tasks', () => {
                if (this.mounted) {
                    console.log('📊 Tasks changed, re-rendering flowchart...');
                    this.mountReact();
                }
            });

            // Watch for selectedTaskId changes and re-render React to highlight node
            this.$watch('selectedTaskId', () => {
                if (this.mounted) {
                    console.log('🎯 Selected task changed:', this.selectedTaskId);
                    this.mountReact();
                }
            });

            // Listen for Livewire events to update flowchart
            window.addEventListener('tasks-updated', () => {
                console.log('📊 Received tasks-updated event');
                if (this.mounted) {
                    this.mountReact();
                }
            });

            window.addEventListener('task-generated', (event) => {
                console.log('📊 Received task-generated event:', event.detail);
                if (this.mounted) {
                    this.mountReact();
                }
            });

            // CRITICAL: Cleanup when Alpine component is destroyed
            // This handles Livewire navigation away from the wizard
            this.$el.addEventListener('destroyed', () => {
                console.log('🧹 Alpine component destroyed, cleaning up React...');
                this.destroy();
            });
        },

        /**
         * Mount or update React component
         * ASYNC because we need to dynamically import FlowchartContainer
         */
        async mountReact() {
            const container = this.$refs.flowchartContainer;

            if (!container) {
                console.error('Flowchart container ref not found');
                return;
            }

            // CRITICAL: Check if container is still connected to DOM
            if (!container.isConnected) {
                console.warn('Flowchart container disconnected, aborting mount');
                return;
            }

            // Verify React is still available
            if (!SharedReact || !SharedReactDOM) {
                console.error('❌ React/ReactDOM not available during mount');
                return;
            }

            // Prevent concurrent mount operations
            if (this.loading) {
                console.log('Mount already in progress, skipping...');
                return;
            }

            // Load the flowchart component dynamically (code-split)
            this.loading = true;
            try {
                const { FlowchartContainer: FlowchartComponent, ReactFlowProvider: Provider } = await loadFlowchartComponent();

                // Get tasks from Alpine data (passed from Livewire)
                const rawTasks = this.tasks || [];
                const readOnly = this.readOnly !== undefined ? this.readOnly : false;
                const layoutDirection = this.layoutDirection || 'vertical';
                const hideValidationPanel = this.hideValidationPanel !== undefined ? this.hideValidationPanel : false;

                // Transform Laravel task structure to React structure
                // Laravel uses 'name', React expects 'title'
                const tasks = rawTasks.map(task => ({
                    ...task,
                    title: task.title || task.name, // Use title if available, fallback to name
                }));

                // Create React props with STABLE callbacks (reuse same function references)
                const props = {
                    tasks: tasks,
                    readOnly: readOnly,
                    layoutDirection: layoutDirection,
                    selectedTaskId: this.selectedTaskId,
                    hideValidationPanel: hideValidationPanel,
                    onTasksChange: this._tasksChangeCallback,
                    onNodeClick: this._nodeClickCallback,
                };

                // Create root only once, then reuse it for updates
                if (!this.reactRoot && container.isConnected) {
                    this.reactRoot = SharedReactDOM.createRoot(container);
                }

                // Only render if we have a valid root
                // IMPORTANT: Wrap FlowchartContainer in ReactFlowProvider to avoid zustand error
                if (this.reactRoot) {
                    this.reactRoot.render(
                        SharedReact.createElement(
                            Provider,
                            null,
                            SharedReact.createElement(FlowchartComponent, props)
                        )
                    );
                    this.mounted = true;
                }

                this.loading = false;

                console.log('⚛️ React flowchart mounted/updated:', {
                    containerId: this.containerId,
                    taskCount: tasks.length,
                    readOnly: readOnly,
                    reactVersion: SharedReact.version
                });
            } catch (error) {
                console.error('❌ Failed to load flowchart component:', error);
                this.loading = false;
            }
        },

        /**
         * Handle node click in flowchart
         * Updates selectedTaskId to sync with task list
         *
         * @param {string} taskId - The ID of the clicked task/node
         */
        handleNodeClick(taskId) {
            console.log('🎯 Node clicked in flowchart:', taskId);
            this.selectedTaskId = taskId;

            // Dispatch event for task list to scroll to and highlight
            this.$dispatch('flowchart-node-clicked', { taskId });

            // Scroll task list item into view
            const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
            if (taskElement) {
                taskElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        /**
         * Handle workflow state updates (node positions, task edits, deletions, etc.)
         * Debounced to avoid excessive Livewire calls
         *
         * NOTE: This now handles ALL workflow changes:
         * - Node position changes (drag and drop)
         * - Task property updates (via TaskDetailsPanel)
         * - Task deletions
         * - Auto-layout and layout direction changes
         *
         * @param {Object} workflowState - { nodes: [...], edges: [...], lastUpdated: ISO string }
         */
        handleWorkflowUpdate(workflowState) {
            if (this.readOnly) return;

            console.log('Workflow state updated:', workflowState);

            // Clear existing timeout
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }

            // Debounce: Save 1 second after last change
            this.saveTimeout = setTimeout(() => {
                this.saveToLivewire(workflowState);
            }, 1000);
        },

        /**
         * Save workflow state to Livewire component
         * @param {Object} workflowState
         */
        saveToLivewire(workflowState) {
            // Check if Livewire wire is available
            if (!this.$wire) {
                console.error('Livewire $wire not available');
                return;
            }

            console.log('Saving workflow state to Livewire...', workflowState);

            // Call Livewire method to save workflow state
            this.$wire.saveWorkflowState(workflowState)
                .then(() => {
                    console.log('Workflow state saved successfully');
                    this.$dispatch('workflow-saved');
                })
                .catch((error) => {
                    console.error('Failed to save workflow state:', error);
                    this.$dispatch('workflow-save-failed', { error });
                });
        },

        /**
         * Cleanup when component is destroyed
         */
        destroy() {
            // CRITICAL FIX: Cancel any pending operations FIRST
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
                this.saveTimeout = null;
            }

            // Mark as not mounted BEFORE unmounting to prevent race conditions
            this.mounted = false;
            this.loading = false;

            try {
                if (this.reactRoot) {
                    // Use setTimeout to defer unmount - prevents React error #185
                    // (state updates during unmount)
                    setTimeout(() => {
                        try {
                            // Double-check the root still exists
                            if (this.reactRoot) {
                                this.reactRoot.unmount();
                                this.reactRoot = null;
                            }
                        } catch (unmountError) {
                            console.warn('React unmount deferred cleanup:', unmountError.message);
                        }
                    }, 0);
                }
            } catch (error) {
                console.warn('React unmount error (non-critical):', error.message);
                // This is expected if the DOM was already cleaned up
            }
        }
    };
}

// Export to window for use in Blade templates
window.flowchartBridge = createFlowchartBridge;
